# ------------------------
# Import libraries
# ------------------------
import io
import os
import math
import heapq
import re
import numpy as np
import pandas as pd
import folium
try:
    import cv2
except ImportError:  # OpenCV optional; fallback overlay used if missing
    cv2 = None
import streamlit as st
import streamlit.components.v1 as components
import tensorflow as tf
from collections import deque
from typing import Dict, Tuple, List, Optional, Set, Any
from tensorflow import keras
from math import radians, sin, cos, sqrt, atan2
from PIL import Image, ImageOps

# -------------------------
# Embedded default map data
# -------------------------
DEFAULT_MAP_TEXT = r"""
[NODES]
1,1.558708,110.340547,Masjid Bandaraya Kuching
2,1.557014,110.343616,Padang Merdeka
3,1.558558,110.344180,Plaza Merdeka
4,1.557108,110.345049,St. Thomas' Cathedral
5,1.556950,110.344800,Padang Merdeka Car Park (South Gate)
6,1.558856,110.344629,Old Courthouse Auditorium
7,1.558300,110.343200,Jalan Pearl Junction
8,1.554494,110.343009,Haji Openg Junction
9,1.551219,110.343969,Sarawak Museum Admin Building
10,1.551910,110.343500,Sarawak Museum (Old Building)
11,1.551450,110.342800,Sarawak Islamic Heritage Museum
12,1.553400,110.341900,Sarawak Art Museum
13,1.557950,110.347900,Kuching Waterfront Jetty
14,1.556000,110.340200,Wisma Hopoh
15,1.553000,110.340800,Heroes Monument

[WAYS]
2001,1,2,Jalan Datuk Ajibah Abol,primary,6
2002,2,1,Jalan Datuk Ajibah Abol,primary,4
2003,2,3,Jalan Tun Abang Haji Openg,secondary,3
2004,3,2,Jalan Tun Abang Haji Openg,secondary,2
2005,2,4,Jalan McDougall,tertiary,4
2006,4,2,Jalan McDougall,tertiary,3
2007,4,5,Jalan McDougall (Cathedral Access),service,2
2008,2,6,Jalan Pearl,secondary,3
2009,6,2,Jalan Pearl,secondary,2
2010,3,6,Jalan Wawasan,secondary,2
2011,6,13,Main Bazaar Road,primary,7
2012,2,8,Jalan Tun Abang Haji Openg,secondary,5
2013,8,2,Jalan Tun Abang Haji Openg,secondary,4
2014,8,9,Jalan Tun Abang Haji Openg,secondary,4
2015,9,8,Jalan Tun Abang Haji Openg,secondary,3
2016,9,10,Jalan Taman Budaya,secondary,5
2017,10,9,Jalan Taman Budaya,secondary,3
2018,9,11,Jalan P. Ramlee,secondary,4
2019,11,9,Jalan P. Ramlee,secondary,3
2020,11,12,Jalan P. Ramlee Extension,secondary,6
2021,12,11,Jalan P. Ramlee Extension,secondary,5
2022,12,15,Jalan Taman Budaya Loop,secondary,3
2023,15,12,Jalan Taman Budaya Loop,secondary,3
2024,1,14,Jalan Masjid,primary,4
2025,14,1,Jalan Masjid,primary,5
2026,14,15,Jalan Satok Connector,secondary,8
2027,5,4,Jalan McDougall Inner,service,3

[CAMERAS]
2001,images/cam_ajibah_abol.jpg
2011,images/cam_main_bazaar.jpg
2016,images/cam_taman_budaya.jpg
2026,images/cam_satok_connector.jpg

[META]
START,1
GOAL,10,13
ACCIDENT_MULTIPLIER,3
"""

GOOGLE_MAP_IFRAME = (
    "<iframe src=\"https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d15953.614541872374!2d110.36663604999998!3d1.5258216!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sen!2smy!4v1765393895740!5m2!1sen!2smy\""
    " width=\"100%\" height=\"300\" style=\"border:0;\" allowfullscreen loading=\"lazy\""
    " referrerpolicy=\"no-referrer-when-downgrade\"></iframe>"
)


def _build_upload_signature(files: List[Any]):
    signature = []
    for file in files:
        size = getattr(file, "size", None)
        if size is None:
            buffer = file.getbuffer()
            size = getattr(buffer, "nbytes", len(buffer))
        signature.append((file.name, size))
    return tuple(signature)

# -------------------------
# Parsing function
# -------------------------
def parse_city_text(text: str):
    nodes: Dict[int, Tuple[float,float,str]] = {}
    ways: Dict[int, Dict[str,Any]] = {}
    edges: Dict[Tuple[int,int], float] = {}
    cameras: Dict[int,str] = {}
    meta: Dict[str,str] = {}
    section = None
    for raw in text.splitlines():
        line = raw.strip()
        if not line or line.startswith("#"):
            continue
        m = re.match(r"^\[(\w+)\]$", line.strip(), flags=re.IGNORECASE)
        if m:
            section = m.group(1).upper()
            continue
        if section == "NODES":
            parts = [p.strip() for p in line.split(",")]
            if len(parts) < 4:
                raise ValueError("Invalid NODES line: " + line)
            nid = int(parts[0]); lat=float(parts[1]); lon=float(parts[2]); label=",".join(parts[3:]).strip()
            nodes[nid] = (lat, lon, label)
        elif section == "WAYS":
            parts = [p.strip() for p in line.split(",")]
            if len(parts) < 6: continue
            wid=int(parts[0]); u=int(parts[1]); v=int(parts[2]); road=parts[3]; highway=parts[4]; base=float(parts[5])
            ways[wid] = {"from":u,"to":v,"road_name":road,"highway":highway,"base_time":base}
            edges[(u,v)] = base
        elif section == "CAMERAS":
            parts=[p.strip() for p in line.split(",")]
            if len(parts)<2: continue
            wid=int(parts[0]); img=",".join(parts[1:]).strip(); cameras[wid]=img
        elif section == "META":
            parts=[p.strip() for p in re.split(r"[,\t]+", line)]
            if not parts: continue
            key=parts[0].upper(); val=",".join(parts[1:]) if len(parts)>1 else ""
            meta[key]=val
    return nodes, ways, edges, cameras, meta

# -------------------------
# ML wrapper 
# -------------------------
ACCIDENT_TYPE_MAP = {
    "none":1.0,
    "minor":2.0,
    "moderate":3.0, 
    "severe":4.0
    }
GLOBAL_MULTIPLIER = 3.0
IMG_SIZE = (224, 224)
FALLBACK_SEVERITY = "none"

class ModelWrapper:
    def __init__(self):
        self.model = None
        self.preprocess_fn = None
        self.class_names = ["none", "minor", "moderate", "severe"]
        self.model_type = None
        self.input_size = IMG_SIZE
        self.grad_model = None
        self.gradcam_layer_name = None
        self.gradcam_backbone_name = None
        self.gradcam_error = None
        self.manual_conv_signature = None

    def load_by_choice(self, choice: str, folder: str = "models"):
        choice = choice.lower()
        fname_map = {
            "resnet": "best_finetuned_ResNet.keras",
            "xception": "best_finetuned_Xception.keras",
            "efficientnet": "best_finetuned_EfficientNet.keras",
        }
        if choice not in fname_map:
            return False, f"Unknown choice: {choice}"
        model_path = os.path.join(folder, fname_map[choice])
        if not os.path.exists(model_path):
            return False, f"Model file not found: {model_path}"

        try:
            self.model = keras.models.load_model(model_path, compile=False)
        except Exception as exc:
            return False, f"Failed to load {choice} model: {exc}"

        self.model_type = choice
        input_shape = getattr(self.model, "input_shape", None)
        if input_shape and len(input_shape) >= 3 and input_shape[1] and input_shape[2]:
            self.input_size = (int(input_shape[1]), int(input_shape[2]))
        else:
            self.input_size = IMG_SIZE

        if choice == "resnet":
            from tensorflow.keras.applications.resnet_v2 import preprocess_input

            self.preprocess_fn = preprocess_input
        elif choice == "xception":
            from tensorflow.keras.applications.xception import preprocess_input

            self.preprocess_fn = preprocess_input
        elif choice == "efficientnet":
            from tensorflow.keras.applications.efficientnet_v2 import preprocess_input

            self.preprocess_fn = preprocess_input
        else:
            self.preprocess_fn = None

        self._init_gradcam_support()
        return True, f"Loaded {choice} model from {model_path}"

    def prepare_for_model(self, pil_image):
        from tensorflow.keras.preprocessing import image as keras_image

        resize_to = self.input_size or IMG_SIZE
        resized = pil_image.resize(resize_to)
        x = keras_image.img_to_array(resized)
        x = np.expand_dims(x, axis=0).astype("float32")
        if self.preprocess_fn:
            try:
                x = self.preprocess_fn(x.copy())
            except Exception:
                pass
        return x

    def predict(self, pil_image):
        if not self.model:
            return FALLBACK_SEVERITY, 1.0
        x = self.prepare_for_model(pil_image)
        try:
            preds = self.model(x, training=False)
        except Exception as exc:
            print("Model prediction error:", exc)
            return FALLBACK_SEVERITY, 1.0
        if isinstance(preds, tf.Tensor):
            preds = preds.numpy()
        preds = np.array(preds)[0]
        idx = int(np.argmax(preds))
        return self.class_names[idx], float(preds[idx])

    def has_gradcam(self) -> bool:
        return bool(self.grad_model) or bool(self.manual_conv_signature)

    # -------------------------
    # Grad-CAM helper functions
    # -------------------------
    def _is_submodel(self, layer):
        if layer is None:
            return False
        if isinstance(layer, keras.Model):
            return True
        return bool(getattr(layer, "layers", []))

    def _shape_is_conv(self, shape_obj):
        if shape_obj is None:
            return False
        try:
            dims = tuple(shape_obj)
        except TypeError:
            return False
        return len(dims) == 4

    def _layer_has_conv_output(self, layer):
        try:
            shape = getattr(layer, "output_shape", None)
        except (AttributeError, ValueError):
            return False
        shapes = shape if isinstance(shape, (list, tuple)) else [shape]
        return any(self._shape_is_conv(shp) for shp in shapes)

    def _extract_conv_layer(self, layer):
        if layer is None:
            return None
        if isinstance(layer, keras.Model):
            return self._find_last_conv_layer_in_model(layer)
        return layer if self._layer_has_conv_output(layer) else None

    def _find_backbone_model(self, model):
        for layer in getattr(model, "layers", []):
            if self._is_submodel(layer):
                return layer
        return None

    def _find_layer_by_name_recursive(self, model, target_name):
        if not model or not target_name:
            return None
        try:
            return model.get_layer(target_name)
        except Exception:
            pass
        for layer in getattr(model, "layers", []):
            if self._is_submodel(layer):
                found = self._find_layer_by_name_recursive(layer, target_name)
                if found:
                    return found
        return None

    def _find_last_conv_layer_in_model(self, model):
        layers = getattr(model, "layers", None)
        if not layers:
            return None
        for layer in reversed(layers):
            if self._is_submodel(layer):
                nested = self._find_last_conv_layer_in_model(layer)
                if nested:
                    return nested
            if self._layer_has_conv_output(layer):
                return layer
        return None

    def _init_gradcam_support(self):
        self.grad_model = None
        self.gradcam_layer_name = None
        self.gradcam_backbone_name = None
        self.gradcam_error = None
        self.manual_conv_signature = None
        if not self.model:
            return

        backbone_hints = {
            "resnet": ["resnet", "resnet50v2"],
            "xception": ["xception"],
            "efficientnet": ["efficientnet", "efficientnetv2"],
        }
        backbone = None
        for hint in backbone_hints.get(self.model_type, []):
            candidate = self._find_layer_by_name_recursive(self.model, hint)
            if self._is_submodel(candidate):
                backbone = candidate
                break
        if not backbone:
            submodels = [layer for layer in self.model.layers if self._is_submodel(layer)]
            for candidate in reversed(submodels):
                if self._find_last_conv_layer_in_model(candidate):
                    backbone = candidate
                    break
        if not backbone:
            backbone = self._find_backbone_model(self.model)

        search_model = backbone or self.model
        if self._is_submodel(backbone):
            self.gradcam_backbone_name = getattr(backbone, "name", None)

        if search_model is not self.model:
            if not self._find_last_conv_layer_in_model(search_model):
                search_model = self.model
                self.gradcam_backbone_name = None

        layer_hints = {
            "resnet": ["post_relu", "conv5_block3_out"],
            "xception": ["block14_sepconv2_act"],
            "efficientnet": ["top_activation"],
        }

        target_layer = None
        for hint in layer_hints.get(self.model_type, []):
            candidate = self._find_layer_by_name_recursive(search_model, hint)
            conv_layer = self._extract_conv_layer(candidate)
            if conv_layer:
                target_layer = conv_layer
                break
        if not target_layer:
            target_layer = self._find_last_conv_layer_in_model(search_model)

        if target_layer:
            self.gradcam_layer_name = target_layer.name
            try:
                self.grad_model = keras.Model(
                    inputs=self.model.inputs,
                    outputs=[target_layer.output, self.model.output],
                    name="gradcam_model",
                )
            except Exception as exc:
                self.grad_model = None
                self.gradcam_error = str(exc)
        else:
            layer_names = [layer.name for layer in getattr(search_model, "layers", [])[-8:]]
            self.gradcam_error = (
                f"No convolutional layer found in '{search_model.name}'. Last layers: "
                + ", ".join(layer_names)
            )

        self.manual_conv_signature = self._detect_manual_conv_signature()
        if not self.grad_model and self.manual_conv_signature and not self.gradcam_layer_name:
            manual_name = (
                self.manual_conv_signature
                if isinstance(self.manual_conv_signature, str)
                else self.manual_conv_signature[1]
            )
            self.gradcam_layer_name = manual_name

    def _detect_manual_conv_signature(self):
        if not self.model:
            return None

        def is_candidate(layer):
            try:
                shape = getattr(layer, "output", None)
                if shape is None:
                    shape = layer.output_shape
            except Exception:
                return False
            shapes = shape if isinstance(shape, (list, tuple)) else [shape]
            for shp in shapes:
                if hasattr(shp, "shape") and not isinstance(shp, tf.TensorShape):
                    shp = shp.shape
                try:
                    dims = tf.TensorShape(shp).as_list()
                except (TypeError, ValueError):
                    continue
                if dims and len(dims) == 4:
                    lname = layer.name.lower()
                    if any(tag in lname for tag in ("input", "augment", "rescaling", "normalization")):
                        continue
                    return True
            return False

        for layer in reversed(getattr(self.model, "layers", [])):
            if is_candidate(layer):
                return layer.name
        for layer in reversed(getattr(self.model, "layers", [])):
            if isinstance(layer, keras.Model):
                for inner in reversed(getattr(layer, "layers", [])):
                    if is_candidate(inner):
                        return (layer.name, inner.name)
        return None

    def _safe_layer_call(self, layer, inputs):
        try:
            if (
                layer == self.model.layers[-1]
                and isinstance(layer, tf.keras.layers.Dense)
                and getattr(layer.activation, "__name__", "") == "softmax"
            ):
                kernel = layer.kernel
                if inputs.dtype != kernel.dtype:
                    kernel = tf.cast(kernel, inputs.dtype)
                outputs = tf.matmul(inputs, kernel)
                if layer.bias is not None:
                    bias = layer.bias
                    if outputs.dtype != bias.dtype:
                        bias = tf.cast(bias, outputs.dtype)
                    outputs = tf.nn.bias_add(outputs, bias)
                return outputs
            return layer(inputs, training=False)
        except TypeError:
            return layer(inputs)

    def _manual_forward_with_capture(self, tensor_input, tape):
        manual_sig = self.manual_conv_signature
        if not manual_sig:
            return None, tensor_input
        x = tf.cast(tensor_input, tf.float32)
        conv_output = None
        found_conv = False
        for layer in self.model.layers:
            if isinstance(layer, tf.keras.layers.InputLayer):
                continue
            if (
                isinstance(manual_sig, tuple)
                and layer.name == manual_sig[0]
                and isinstance(layer, keras.Model)
            ):
                parent_name, inner_name = manual_sig
                try:
                    sub_model = keras.Model(
                        inputs=layer.inputs,
                        outputs=[layer.get_layer(inner_name).output, layer.output],
                    )
                    conv_out, layer_out = sub_model(x, training=False)
                    conv_output = conv_out
                    tape.watch(conv_output)
                    x = layer_out
                    found_conv = True
                    continue
                except Exception:
                    pass
            x = self._safe_layer_call(layer, x)
            if isinstance(manual_sig, str) and layer.name == manual_sig:
                conv_output = x
                tape.watch(conv_output)
                found_conv = True
        if not found_conv:
            return None, x
        return conv_output, x

    def _make_gradcam_heatmap(self, pil_image, pred_index=None):
        if not self.grad_model:
            return None
        try:
            img_array = self.prepare_for_model(pil_image)
            img_tensor = tf.convert_to_tensor(img_array)
            with tf.GradientTape() as tape:
                conv_outputs, predictions = self.grad_model(img_tensor, training=False)
                if pred_index is None:
                    pred_index = tf.argmax(predictions[0])
                target_channel = predictions[:, pred_index]
            grads = tape.gradient(target_channel, conv_outputs)
            if grads is None:
                return None
            pooled_grads = tf.reduce_mean(grads, axis=(0, 1, 2))
            conv_outputs = conv_outputs[0]
            heatmap = tf.reduce_sum(conv_outputs * pooled_grads, axis=-1)
            heatmap = tf.maximum(heatmap, 0)
            denom = tf.reduce_max(heatmap)
            denom_val = float(denom.numpy())
            if denom_val <= 0:
                return None
            heatmap /= denom
            return heatmap.numpy()
        except Exception as exc:
            self.gradcam_error = str(exc)
            return None

    def _manual_gradcam_heatmap(self, pil_image, pred_index=None):
        if not self.manual_conv_signature:
            return None
        try:
            img_array = self.prepare_for_model(pil_image)
            img_tensor = tf.convert_to_tensor(img_array)
            with tf.GradientTape() as tape:
                conv_output, logits = self._manual_forward_with_capture(img_tensor, tape)
                if conv_output is None:
                    return None
                if pred_index is None:
                    pred_index = tf.argmax(logits[0])
                class_channel = logits[:, pred_index]
            grads = tape.gradient(class_channel, conv_output)
            if grads is None:
                return None
            pooled_grads = tf.reduce_mean(grads, axis=(0, 1, 2))
            conv_outputs = conv_output[0]
            conv_outputs = tf.cast(conv_outputs, tf.float32)
            pooled_grads = tf.cast(pooled_grads, tf.float32)
            heatmap = tf.reduce_sum(conv_outputs * pooled_grads, axis=-1)
            heatmap = tf.maximum(heatmap, 0)
            denom = tf.reduce_max(heatmap)
            denom_val = float(denom.numpy())
            if denom_val <= 0:
                return None
            heatmap /= denom
            return heatmap.numpy()
        except Exception as exc:
            self.gradcam_error = str(exc)
            return None

    def _overlay_gradcam(self, image_array, heatmap, alpha=0.4):
        try:
            if image_array.dtype != np.uint8:
                image_array = image_array.astype("uint8")
            h, w = image_array.shape[:2]
            heatmap = np.clip(heatmap, 0.0, 1.0)
            heatmap_resized = np.array(
                Image.fromarray(np.uint8(heatmap * 255), mode="L").resize((w, h))
            ) / 255.0
            if cv2 is not None:
                heatmap_uint8 = np.uint8(255 * heatmap_resized)
                heatmap_color = cv2.applyColorMap(heatmap_uint8, cv2.COLORMAP_JET)
                image_bgr = cv2.cvtColor(image_array, cv2.COLOR_RGB2BGR)
                superimposed = cv2.addWeighted(image_bgr, 1 - alpha, heatmap_color, alpha, 0)
                overlay_rgb = cv2.cvtColor(superimposed, cv2.COLOR_BGR2RGB)
                return Image.fromarray(overlay_rgb)
            base = Image.fromarray(image_array)
            heatmap_img = Image.fromarray(np.uint8(heatmap_resized * 255), mode="L")
            colored = ImageOps.colorize(heatmap_img, black="blue", white="red").convert("RGB")
            return Image.blend(base, colored, alpha)
        except Exception:
            return None

    def generate_gradcam_visuals(self, pil_image, alpha=0.4):
        if not self.has_gradcam():
            return None, None, self.gradcam_error or "Grad-CAM not available"
        image_array = np.array(pil_image)
        errors = []
        if self.grad_model:
            heatmap = self._make_gradcam_heatmap(pil_image)
            if heatmap is not None:
                overlay = self._overlay_gradcam(image_array, heatmap, alpha)
                if overlay is not None:
                    return heatmap, overlay, None
                errors.append("Unable to overlay Grad-CAM heatmap")
            else:
                errors.append("Unable to compute Grad-CAM heatmap")
        if self.manual_conv_signature:
            heatmap = self._manual_gradcam_heatmap(pil_image)
            if heatmap is not None:
                overlay = self._overlay_gradcam(image_array, heatmap, alpha)
                if overlay is not None:
                    return heatmap, overlay, None
                errors.append("Unable to overlay Grad-CAM heatmap")
            else:
                errors.append("Unable to compute Grad-CAM heatmap")
        message = errors[-1] if errors else (self.gradcam_error or "Grad-CAM unavailable")
        return None, None, message

    def compute_edge_times_by_road(
        self,
        ways,
        edges,
        road_name,
        severity,
        accident_type_map=ACCIDENT_TYPE_MAP,
    ):
        edge_times = {}
        severity_key = (severity or "").lower()
        factor = float(accident_type_map.get(severity_key, 1.0))
        for wid, info in ways.items():
            u, v = info["from"], info["to"]
            base = float(info.get("base_time", edges.get((u, v), 0.0)))
            if road_name and road_name.lower() in info["road_name"].lower() and severity_key != "none":
                adjusted = base * factor * GLOBAL_MULTIPLIER
            else:
                adjusted = base
            edge_times[(u, v)] = adjusted
        return edge_times

# -------------------------
# Geometry helpers
# -------------------------

# Euclidean distance (straight-line distance)
def euclidean(a,b):
    return math.sqrt((a[0]-b[0])**2 + (a[1]-b[1])**2)

# Haversine distance (great-circle distance on Earth)
def haversine(coord1, coord2):
    R = 6371.0
    lat1, lon1 = radians(coord1[0]), radians(coord1[1])
    lat2, lon2 = radians(coord2[0]), radians(coord2[1])
    dlat = lat2 - lat1
    dlon = coord2[1] - coord1[1]
    dlon = radians(dlon)
    a = sin(dlat/2)**2 + cos(lat1) * cos(lat2) * sin(dlon/2)**2
    c = 2 * atan2(sqrt(1 - a), sqrt(a))
    # Note: Above was a bug; fix to classic haversine:
    dlat = lat2 - lat1
    dlon = radians(coord2[1] - coord1[1])
    a = sin(dlat/2)**2 + cos(lat1) * cos(lat2) * sin(dlon/2)**2
    c = 2 * atan2(sqrt(a), sqrt(1-a))
    return R * c

# -------------------------
# Search Algorithms
# -------------------------

# GBFS
def gbfs(nodes, edges, edge_times, origin, destination):
    from queue import PriorityQueue
    visited = set()
    frontier = PriorityQueue()
    h_origin = euclidean(nodes[origin][:2], nodes[destination][:2])
    frontier.put((h_origin, [origin]))
    num_created = 1
    while not frontier.empty():
        _, path = frontier.get()
        current = path[-1]
        if current == destination:
            return current, num_created, path
        if current not in visited:
            visited.add(current)
            neighbors = [node2 for (node1, node2) in edges.keys() if node1 == current]
            for neighbor in neighbors:
                if neighbor not in visited:
                    h = euclidean(nodes[neighbor][:2], nodes[destination][:2])
                    frontier.put((h, path + [neighbor]))
                    num_created += 1
    return None, num_created, []

# DFS
def dfs(edges, edge_times, start, goals):
    stack = [(start, [start])]
    visited = set()
    nodes_created = 1
    frontier = {start}
    while stack:
        node, path = stack.pop()
        frontier.discard(node)
        if node not in visited:
            visited.add(node)
            nodes_created += 1
            if node in goals:
                total_nodes = len(visited.union(frontier))
                return node, total_nodes, path
            neighbors = sorted([n2 for (n1,n2) in edges if n1 == node])
            for next_node in reversed(neighbors):
                if next_node not in visited:
                    stack.append((next_node, path + [next_node]))
                    frontier.add(next_node)
    return None, nodes_created, []

# BFS
def bfs(origin, destinations, edges, edge_times):
    queue = deque([(origin, [origin])])
    visited = set([origin])
    count = 1
    while queue:
        node, path = queue.popleft()
        neighbors = sorted(node2 for (node1,node2) in edges.keys() if node1 == node)
        for neighbor in neighbors:
            if neighbor not in visited:
                visited.add(neighbor)
                count += 1
                new_path = path + [neighbor]
                if neighbor in destinations:
                    return neighbor, count, new_path
                queue.append((neighbor, new_path))
    return None, count, []

# A*
def astar(nodes, edges, edge_times, origin, destinations):
    dest_set = set(destinations)
    if origin in dest_set:
        return origin, 1, [origin]
    frontier = []
    start_h = min(haversine(nodes[origin][:2], nodes[d][:2]) for d in dest_set)
    heapq.heappush(frontier, (start_h, start_h, origin, 0.0))
    came_from = {}
    g_scores = {origin: 0.0}
    nodes_created = 1
    while frontier:
        f_curr, h_curr, current, g_actual = heapq.heappop(frontier)
        if g_actual > g_scores.get(current, float('inf')):
            continue
        if current in dest_set:
            path = []
            node = current
            while node != origin:
                path.append(node)
                node = came_from[node]
            path.append(origin)
            path.reverse()
            return current, nodes_created, path
        neighbors = sorted(node2 for (node1, node2) in edges.keys() if node1 == current)
        for neighbor in neighbors:
            weight = edge_times.get((current, neighbor), edges.get((current, neighbor), float('inf')))
            tentative_g = g_actual + weight
            if tentative_g < g_scores.get(neighbor, float('inf')):
                g_scores[neighbor] = tentative_g
                came_from[neighbor] = current
                h_neighbor = min(haversine(nodes[neighbor][:2], nodes[d][:2]) for d in dest_set)
                f_neighbor = tentative_g + h_neighbor
                heapq.heappush(frontier, (f_neighbor, h_neighbor, neighbor, tentative_g))
                nodes_created += 1
    return None, nodes_created, []

# UCS
def ucs(origin, destinations, edges, edge_times):
    dest_set = set(destinations)
    frontier = []
    heapq.heappush(frontier, (0.0, origin, [origin]))
    visited = set()
    pushes = 1
    while frontier:
        cost, node, path = heapq.heappop(frontier)
        if node in visited:
            continue
        visited.add(node)
        if node in dest_set:
            return node, pushes, path
        for (u,v), w in edges.items():
            if u != node: continue
            if v in visited: continue
            travel_time = edge_times.get((u,v), w)
            heapq.heappush(frontier, (cost + travel_time, v, path + [v]))
            pushes += 1
    return None, pushes, []

# ILMS
def ilms(nodes, edges, edge_times, start, goals):
    def heuristic(n1: int):
        x1,y1 = nodes[n1][0], nodes[n1][1]
        return min(math.sqrt((x1 - nodes[g][0])**2 + (y1 - nodes[g][1])**2) for g in goals)
    pq=[]
    counter=0
    heapq.heappush(pq, (heuristic(start), 0, start, counter, [start]))
    visited=set()
    frontier=set([start])
    while pq:
        f,g,node,_,path = heapq.heappop(pq)
        frontier.discard(node)
        if node in visited: continue
        visited.add(node)
        if node in goals:
            total_nodes = len(visited.union(frontier))
            return node, total_nodes, path
        neighbors = sorted([n2 for (n1,n2) in edges if n1 == node])
        for n2 in neighbors:
            if n2 not in visited and n2 not in frontier:
                new_g = g + 1
                new_f = new_g + heuristic(n2)
                counter += 1
                heapq.heappush(pq, (new_f, new_g, n2, counter, path + [n2]))
                frontier.add(n2)
    total_nodes = len(visited.union(frontier))
    return None, total_nodes, []

# Wrapper to  run chosen algorithm by name- computes total travel time along path
def run_algo_by_name(algo_name: str, nodes, edges, edge_times, origin, dest):
    algo = algo_name.lower()
    if algo == "gbfs":
        goal, nodes_created, path = gbfs(nodes, edges, edge_times, origin, dest)
    elif algo == "dfs":
        goal, nodes_created, path = dfs(edges, edge_times, origin, {dest})
    elif algo == "bfs":
        goal, nodes_created, path = bfs(origin, [dest], edges, edge_times)
    elif algo == "astar":
        goal, nodes_created, path = astar(nodes, edges, edge_times, origin, [dest])
    elif algo == "ucs":
        goal, nodes_created, path = ucs(origin, [dest], edges, edge_times)
    elif algo == "ilms":
        goal, nodes_created, path = ilms(nodes, edges, edge_times, origin, {dest})
    else:
        raise ValueError("Unknown algorithm: " + algo_name)
    if not path:
        return None, [], nodes_created
    total = 0.0
    for a,b in zip(path, path[1:]):
        total += edge_times.get((a,b), edges.get((a,b), 0.0))
    return total, path, nodes_created

# Finds K alternative paths, ensuring multiple distinct routes
def yen_k_shortest_using_user_algorithms(nodes, edges_costs, source, target, algo_name, K=5):
    """
    Returns up to K paths using Yen's algorithm, driven by chosen algo for spur pathing.
    """
    A: List[Tuple[float,List[int]]] = []
    B: List[Tuple[float,List[int]]] = []  # min-heap

    first_cost, first_path, _ = run_algo_by_name(algo_name, nodes, edges_costs, edges_costs, source, target)
    if not first_path:
        return []
    A.append((first_cost, first_path))

    for k in range(1, K):
        last_path = A[k-1][1]
        for i in range(len(last_path) - 1):
            root_path = last_path[:i+1]
            spur_node = root_path[-1]

            removed_edges = set()
            removed_nodes = set()

            for (cost_p, path_p) in A:
                if len(path_p) > i and path_p[:i+1] == root_path:
                    removed_edges.add((path_p[i], path_p[i+1]))

            for rn in root_path[:-1]:
                removed_nodes.add(rn)

            modified = {}
            for (u,v), c in edges_costs.items():
                if (u,v) in removed_edges: continue
                if u in removed_nodes or v in removed_nodes: continue
                modified[(u,v)] = c

            spur_cost, spur_path, _ = run_algo_by_name(algo_name, nodes, modified, modified, spur_node, target)
            if not spur_path:
                continue
            total_path = root_path[:-1] + spur_path
            total_cost = 0.0
            valid = True
            for a,b in zip(total_path, total_path[1:]):
                if (a,b) not in edges_costs:
                    valid = False
                    break
                total_cost += edges_costs[(a,b)]
            if not valid:
                continue
            if any(p == total_path for (_,p) in A) or any(p == total_path for (_,p) in B):
                continue
            heapq.heappush(B, (total_cost, total_path))
        if not B:
            break
        smallest = heapq.heappop(B)
        A.append(smallest)
    return A[:K]

# -------------------------
# Route visualization helpers
# -------------------------
def build_route_map_html(latest_route: Dict[str, Any], nodes: Dict[int, Tuple[float, float, str]]):
    """Return HTML for a folium map highlighting every computed route."""
    if not latest_route or not latest_route.get("paths"):
        return None

    unique_nodes: Set[int] = set()
    for _, path in latest_route["paths"]:
        unique_nodes.update(path)
    latitudes = [nodes[n][0] for n in unique_nodes if n in nodes]
    longitudes = [nodes[n][1] for n in unique_nodes if n in nodes]
    if not latitudes or not longitudes:
        fallback = next(iter(nodes.values()))
        latitudes = [fallback[0]]
        longitudes = [fallback[1]]
    center_lat = sum(latitudes) / len(latitudes)
    center_lon = sum(longitudes) / len(longitudes)

    fmap = folium.Map(location=[center_lat, center_lon], zoom_start=15, tiles="cartodbpositron")
    palette = ["#ef4444", "#3b82f6", "#10b981", "#f97316", "#a855f7"]

    for idx, (_, path) in enumerate(latest_route["paths"]):
        coords = [[nodes[n][0], nodes[n][1]] for n in path if n in nodes]
        if len(coords) < 2:
            continue
        folium.PolyLine(
            coords,
            color=palette[idx % len(palette)],
            weight=6,
            opacity=0.9,
            tooltip=f"Path #{idx+1}"
        ).add_to(fmap)

    origin = latest_route.get("origin")
    destination = latest_route.get("destination")
    if origin in nodes:
        folium.Marker(
            [nodes[origin][0], nodes[origin][1]],
            popup=f"Origin: {nodes[origin][2]}",
            icon=folium.Icon(color="green", icon="play", prefix="fa")
        ).add_to(fmap)
    if destination in nodes:
        folium.Marker(
            [nodes[destination][0], nodes[destination][1]],
            popup=f"Destination: {nodes[destination][2]}",
            icon=folium.Icon(color="orange", icon="flag", prefix="fa")
        ).add_to(fmap)

    return fmap._repr_html_()

# -------------------------
# Camera prediction helper
# -------------------------

# Runs accident severity prediction on all embedded CCTV camera images
def batch_predict_cameras(model_wrapper: Optional[ModelWrapper], cameras: Dict[int,str]):
    preds = {}
    if not model_wrapper or not model_wrapper.model:
        for wid in cameras.keys():
            preds[wid] = ("none", 1.0)
        return preds
    for wid, path in cameras.items():
        try:
            if not path or not os.path.exists(path):
                preds[wid] = ("none", 1.0)
                continue
            pil = Image.open(path).convert("RGB")
            cls, prob = model_wrapper.predict(pil)
            preds[wid] = (cls, prob)
        except Exception:
            preds[wid] = ("none", 1.0)
    return preds

# -------------------------
# Streamlit UI
# -------------------------
st.set_page_config(page_title="Traffic Route Monitor", layout="wide")

# Session state defaults
if "edge_times" not in st.session_state:
    st.session_state.edge_times = None
if "severity_class" not in st.session_state:
    st.session_state.severity_class = "none"
if "severity_conf" not in st.session_state:
    st.session_state.severity_conf = 1.0
if "incident_road_applied" not in st.session_state:
    st.session_state.incident_road_applied = ""
if "latest_route" not in st.session_state:
    st.session_state.latest_route = None
if "pending_upload_signature" not in st.session_state:
    st.session_state.pending_upload_signature = None
if "last_processed_upload_signature" not in st.session_state:
    st.session_state.last_processed_upload_signature = None
if "gradcam_gallery" not in st.session_state:
    st.session_state.gradcam_gallery = []

st.title("Traffic Route Monitor")
st.caption("Real-time route optimization with incident detection")

nodes, ways, edges, cameras, meta = parse_city_text(DEFAULT_MAP_TEXT)

# Sidebar: algorithm, K, model
with st.sidebar:
    st.header("Settings")
    algo = st.selectbox("Algorithm", ["ucs","astar","gbfs","bfs","dfs","ilms"], index=0)
    K = st.slider("Number of paths (K)", 1, 5, 5)
    st.markdown("---")
    st.header("Model selection")
    model_choice = st.selectbox("Accident severity model", ["None","ResNet","Xception","EfficientNet"], index=0)
    st.info("Place .keras files in ./models/: resnet_model.keras, xception_model.keras, efficientnet_model.keras")
    
# Build labels
node_labels = {nid: nodes[nid][2] for nid in nodes}
label_to_id = {label: nid for nid, (_,_,label) in nodes.items()}
labels_sorted = [node_labels[nid] for nid in sorted(nodes.keys())]

# Layout
col_route, col_cctv = st.columns([1,1])
current_upload_signature = None
show_gradcam = False

with col_route:
    st.subheader("Route planning")
    origin_label = st.selectbox("Origin", labels_sorted, index=labels_sorted.index(node_labels[int(meta.get("START","1"))]) if meta.get("START") else 0)
    dest_label = st.selectbox("Destination", labels_sorted, index=labels_sorted.index(node_labels[int(meta.get("GOAL","10").split(",")[0])]) if meta.get("GOAL") else len(labels_sorted)-1)
    origin = label_to_id[origin_label]
    destination = label_to_id[dest_label]

    st.markdown("**Route map:**")
    latest = st.session_state.latest_route
    map_html = build_route_map_html(latest, nodes) if latest else None
    if map_html:
        components.html(map_html, height=420)
        origin_label_txt = node_labels.get(latest["origin"], str(latest["origin"]))
        dest_label_txt = node_labels.get(latest["destination"], str(latest["destination"]))
        st.markdown(f"**Origin:** {origin_label_txt}")
        st.markdown(f"**Destination:** {dest_label_txt}")
        st.caption("Colored polylines trace every Top-K route between the selected points.")
    else:
        st.markdown(GOOGLE_MAP_IFRAME, unsafe_allow_html=True)
        st.info("Click 'Compute routes' to display the optimal path on the map.")


with col_cctv:
    st.subheader("CCTV incident analysis")
    uploaded_imgs = st.file_uploader("Drop your CCTV frame here", type=["jpg","jpeg","png"], accept_multiple_files=True)
    if uploaded_imgs:
        st.caption("Uploads are analyzed automatically when a model is selected.")
        preview_cols = st.columns(min(3, len(uploaded_imgs)))
        for idx, file in enumerate(uploaded_imgs):
            col = preview_cols[idx % len(preview_cols)]
            file.seek(0)
            col.image(file, caption=f"Image {idx+1}", use_container_width=True)
            file.seek(0)
    current_upload_signature = _build_upload_signature(uploaded_imgs) if uploaded_imgs else None
    if current_upload_signature and current_upload_signature != st.session_state.last_processed_upload_signature:
        st.session_state.pending_upload_signature = current_upload_signature
    elif not uploaded_imgs:
        st.session_state.pending_upload_signature = None
    road_options = sorted(set(info["road_name"] for info in ways.values()))
    incident_road = st.selectbox("Incident location (road name)", [""] + road_options)
    st.caption("Tip: Matching is case-insensitive substring.")
    show_gradcam = st.checkbox("Show Grad-CAM heatmap", value=False)
    analyze_btn = st.button("Analyze with AI")  # Manual re-run button

# Initialize model
model_wrapper = None
severity_pred = ("none", 1.0)
if model_choice != "None":
    model_wrapper = ModelWrapper()
    choice_map = {"ResNet":"resnet","Xception":"xception","EfficientNet":"efficientnet"}
    ok, msg = model_wrapper.load_by_choice(choice_map[model_choice])
    if ok:
        st.sidebar.success(msg)
        if model_wrapper.has_gradcam():
            label = model_wrapper.gradcam_layer_name or "last_conv"
            if model_wrapper.grad_model:
                st.sidebar.caption(f"Grad-CAM ready — layer `{label}`")
            else:
                st.sidebar.caption(f"Grad-CAM ready (manual) — layer `{label}`")
            if model_wrapper.gradcam_backbone_name:
                st.sidebar.caption(
                    f"Backbone: {model_wrapper.gradcam_backbone_name}"
                )
        else:
            warn = model_wrapper.gradcam_error or "Grad-CAM layers not found in this model; heatmaps disabled."
            st.sidebar.warning(warn)
    else:
        st.sidebar.error(msg)
auto_trigger = bool(
    uploaded_imgs
    and current_upload_signature
    and st.session_state.pending_upload_signature
    and st.session_state.pending_upload_signature == current_upload_signature
    and model_wrapper
    and model_wrapper.model
)
if st.session_state.pending_upload_signature and uploaded_imgs and not (model_wrapper and getattr(model_wrapper, "model", None)):
    st.info("Select a model to analyze your uploads automatically.")

should_run_analysis = (
    (analyze_btn or auto_trigger)
    and uploaded_imgs
    and model_wrapper
    and model_wrapper.model
)

if should_run_analysis:
    if auto_trigger:
        st.info("Detected new uploads. Running automatic accident analysis...")
    gradcam_entries = []
    for i, file in enumerate(uploaded_imgs, start=1):
        try:
            file.seek(0)
            pil = Image.open(file).convert("RGB")
            cls, prob = model_wrapper.predict(pil)
            st.session_state.severity_class = cls
            st.session_state.severity_conf = prob

            st.markdown(f"**Image {i}:** Predicted severity = {cls}, Confidence = {prob:.2f}")

            heatmap, overlay, gradcam_err = model_wrapper.generate_gradcam_visuals(pil)
            gradcam_entries.append({
                "image": pil.copy(),
                "heatmap": heatmap,
                "overlay": overlay,
                "label": cls,
                "prob": prob,
                "index": i,
                "error": gradcam_err
            })
        except Exception as e:
            st.session_state.severity_class = FALLBACK_SEVERITY
            st.session_state.severity_conf = 1.0
            st.warning(f"Image {i} failed to process, defaulting to '{FALLBACK_SEVERITY}' severity.")
            st.exception(e)
    st.session_state.gradcam_gallery = gradcam_entries

    if incident_road.strip():
        st.session_state.edge_times = model_wrapper.compute_edge_times_by_road(
            ways, edges, incident_road.strip(), st.session_state.severity_class
        )
        st.session_state.incident_road_applied = incident_road.strip()
        st.success(f"Applied severity '{st.session_state.severity_class}' to ways containing '{incident_road.strip()}'.")
    else:
        st.session_state.edge_times = {(u,v): float(c) for (u,v), c in edges.items()}
        st.session_state.incident_road_applied = ""
        st.warning("No incident road provided. Using base edge times.")

    if current_upload_signature:
        st.session_state.last_processed_upload_signature = current_upload_signature
        st.session_state.pending_upload_signature = None

with col_cctv:
    gradcam_gallery = st.session_state.get("gradcam_gallery", [])
    if show_gradcam:
        st.markdown("**Grad-CAM heatmaps**")
        if not gradcam_gallery:
            st.caption("No Grad-CAM results yet. Run the analysis to generate heatmaps.")
        for entry in gradcam_gallery:
            overlay = entry.get("overlay")
            if overlay is None:
                reason = entry.get("error") or "Grad-CAM not available for this model."
                st.caption(f"Image {entry.get('index', '?')}: {reason}")
                continue
            label = entry.get("label", "")
            prob = entry.get("prob", 0.0)
            st.image(
                overlay,
                caption=f"Image {entry.get('index', '?')}: {label} (conf {prob:.2f})",
                use_container_width=True
            )
    elif gradcam_gallery:
        st.caption("Enable the Grad-CAM checkbox to view the latest heatmaps.")

# Prepare edge_times for routing: prefer analyzed results, else base
current_edge_times = st.session_state.edge_times or {(u,v): float(c) for (u,v), c in edges.items()}

st.markdown("---")
st.subheader("Top-K routes")
if st.session_state.incident_road_applied:
    st.caption(f"Incident applied on: {st.session_state.incident_road_applied} — Severity: {st.session_state.severity_class} (conf {st.session_state.severity_conf:.2f})")
go_btn = st.button("Compute routes")

if go_btn:
    results = yen_k_shortest_using_user_algorithms(nodes, current_edge_times, origin, destination, algo, K)
    if not results:
        st.error("No path found.")
        st.session_state.latest_route = None
    else:
        st.session_state.latest_route = {
            "paths": results,
            "algo": algo,
            "origin": origin,
            "destination": destination,
        }
        st.rerun()

latest = st.session_state.latest_route
if latest:
    st.markdown(f"**Current route set** — {node_labels[latest['origin']]} ➝ {node_labels[latest['destination']]} (algo: {latest['algo'].upper()})")
    for i, (cost, path) in enumerate(latest["paths"], 1):
        st.markdown(f"**Path #{i}:** total_estimated_time = {cost:.2f}")
        labels = [node_labels[n] for n in path]
        st.write(" -> ".join(labels))
    st.caption("Note: UCS and A* are cost-aware; other algorithms may return paths that aren’t the true K best by cost.")