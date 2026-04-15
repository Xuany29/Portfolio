#include <stdio.h>
#include <stdbool.h>
#include <time.h>
#include <SDL2/SDL.h>
#include <SDL2/SDL_image.h>
#include <SDL2/SDL_ttf.h>

#define INTERVAL_ADD_BULLETS_MS 300
#define INTERVAL_MOVE_BULLETS_MS 20
#define INTERVAL_ADD_ENEMIES_MS 300
#define INTERVAL_MOVE_ENEMIES_MS 100
#define INTERVAL_ADD_BULLETS_ENEMY_MS 1200
#define INTERVAL_MOVE_BULLETS_ENEMY_MS 40
#define INTERVAL_ADD_BULLETS_BOSS_MS 500
#define INTERVAL_MOVE_BULLETS_BOSS_MS 50
#define INTERVAL_MOVE_BOSS_MS 100
#define INTERVAL_ADD_PILL_MS 5000
#define MAX_BULLETS 10
#define MAX_BULLETS_ENEMY 100
#define MAX_BULLETS_BOSS 100
#define MAX_ENEMIES 4
#define MAX_PILLS 1

uint64_t last_tick_bullet_add;
uint64_t last_tick_bullet_move;
uint64_t last_tick_enemy_add;
uint64_t last_tick_enemy_move;
uint64_t last_tick_boss_move;
uint64_t last_tick_bullet_enemy_add;
uint64_t last_tick_bullet_enemy_move;
uint64_t last_tick_bullet_boss_add;
uint64_t last_tick_bullet_boss_move;
uint64_t last_tick_pill_add;

int bullet_count = 0;
int enemy_count = 0;
int bullet_enemy_count = 0;
int bullet_boss_count = 0;
int pill_count = 0;

//initialize screen width and height
const int SCREEN_WIDTH = 800;
const int SCREEN_HEIGHT = 600;

typedef struct player {
    int x;
    int y;
    SDL_Texture *texture_player;
} Player;

typedef struct enemy {
    int x;
    int y;
    int dir_x;
    int dir_y;
    int enemy_index;
    bool available;
    SDL_Texture *texture_enemy;
} Enemy;

typedef struct boss {
    int x;
    int y;
    int dir_x;
    int dir_y;
    int hp;
    SDL_Texture *texture_boss;
} Boss;

typedef struct bullet {
    int x;
    int y;
    int speed;
    SDL_Texture *texture_bullet;
} Bullet;

typedef struct bulletEnemy {
    int x;
    int y;
    int speed;
    SDL_Texture *texture_bullet_enemy;
} BulletEnemy;

typedef struct bulletBoss {
    int x;
    int y;
    int speed;
    SDL_Texture *texture_bullet_boss;
} BulletBoss;

typedef struct pill {
    int x;
    int y;
    bool available;
    SDL_Texture *texture_pill;
} Pill;

void draw_menu(SDL_Renderer *renderer, TTF_Font *font, char *name) {
    // Draw black background
    SDL_SetRenderDrawColor(renderer, 0, 0, 0, 255);
    SDL_RenderClear(renderer);

    SDL_Color text_color = {255, 255, 255, 255};

    // Render the prompt text
    SDL_Surface *surface = TTF_RenderText_Solid(font, "Please Enter Your Name: ", text_color);
    SDL_Texture *texture = SDL_CreateTextureFromSurface(renderer, surface);
    int texW = 0, texH = 0;
    SDL_QueryTexture(texture, NULL, NULL, &texW, &texH);
    SDL_Rect dstrect = {SCREEN_WIDTH / 2 - texW / 2, SCREEN_HEIGHT / 2 - texH / 2 - 50, texW, texH};

    SDL_RenderCopy(renderer, texture, NULL, &dstrect);
    SDL_RenderPresent(renderer); // Update the screen to show the prompt text


    // Initialize name input
    name[0] = '\0'; 
    int name_len = 0;

    SDL_StartTextInput();
    SDL_Event event;
    bool start = false;
    //Handle user input
    while (!start) {
        while (SDL_PollEvent(&event)) {
            if (event.type == SDL_QUIT) {
                exit(0);
            } else if (event.type == SDL_KEYDOWN && event.key.keysym.sym == SDLK_RETURN) {
                if (name_len > 0) {
                    start = true;
                }
            } else if (event.type == SDL_TEXTINPUT) {
                if (name_len < 254) {
                    strcat(name, event.text.text);
                    name_len += strlen(event.text.text);
                }
            } else if (event.type == SDL_KEYDOWN && event.key.keysym.sym == SDLK_BACKSPACE && name_len > 0) {
                name[--name_len] = '\0';
            }
        }
        // Render the name input
        SDL_Surface *name_surface = TTF_RenderText_Solid(font, name, text_color);
        SDL_Texture *name_texture = SDL_CreateTextureFromSurface(renderer, name_surface);
        SDL_QueryTexture(name_texture, NULL, NULL, &texW, &texH);
        SDL_Rect name_rect = {SCREEN_WIDTH / 2 - texW / 2, SCREEN_HEIGHT / 2, texW, texH};

        SDL_RenderClear(renderer);
        SDL_RenderCopy(renderer, texture, NULL, &dstrect); // Re-render the prompt text
        SDL_RenderCopy(renderer, name_texture, NULL, &name_rect);
        SDL_RenderPresent(renderer);

        SDL_DestroyTexture(name_texture);
        SDL_FreeSurface(name_surface);
    }
    SDL_StopTextInput();
    SDL_DestroyTexture(texture);
    SDL_FreeSurface(surface);
}

void read_highest(FILE *fptr, int *highest, char *name) {
    if ((fptr = fopen("highest.txt", "r")) == NULL) {
        printf("Unable to open file.");
    } else {
        fscanf(fptr, "%s\n", name);
        fscanf(fptr, "%i\n", highest);
        fclose(fptr);
    }
}

void save_highest(FILE *fptr, int highest, char *name) {
    if ((fptr = fopen("highest.txt", "w")) == NULL) {
        printf("Unable to open file.");
    } else {
        fprintf(fptr, "%s\n", name);
        fprintf(fptr, "%i\n", highest);
        fclose(fptr);
    }
}

bool draw_gameOver(SDL_Renderer *renderer, TTF_Font *font, int score, int highest) {
    //Draw black background
    SDL_SetRenderDrawColor(renderer, 0, 0, 0, 255);
    SDL_RenderClear(renderer);

    SDL_Color text_color = {255, 0, 0,255};

    char text[255];
    sprintf(text, "Game Over! Your Score: %d", score);

    SDL_Surface *surface = TTF_RenderText_Solid(font, text, text_color);
    SDL_Texture *texture = SDL_CreateTextureFromSurface(renderer, surface);
    int texW = 0, texH = 0;
    SDL_QueryTexture(texture, NULL, NULL, &texW, &texH);
    SDL_Rect dstrect = {SCREEN_WIDTH / 2 - texW / 2, SCREEN_HEIGHT / 2 - texH / 2, texW, texH};

    SDL_RenderCopy(renderer, texture, NULL, &dstrect);

    char high[255];
    sprintf(high, "Highest Score: %d", highest);

    SDL_Surface *hsurface = TTF_RenderText_Solid(font, high, text_color);
    SDL_Texture *htexture = SDL_CreateTextureFromSurface(renderer, hsurface);
    int htexW = 0, htexH = 0;
    SDL_QueryTexture(htexture, NULL, NULL, &htexW, &htexH);
    SDL_Rect hrect = {SCREEN_WIDTH / 2 - htexW / 2, SCREEN_HEIGHT / 2 - htexH / 2 + 20, htexW, htexH};

    SDL_RenderCopy(renderer, htexture, NULL, &hrect);

    SDL_RenderPresent(renderer);

    SDL_DestroyTexture(texture);
    SDL_DestroyTexture(htexture);
    SDL_FreeSurface(surface);
    SDL_FreeSurface(hsurface);

    SDL_Event event;
    bool restart = false;
    while (!restart) {
        while (SDL_PollEvent(&event)) {
            if (event.type == SDL_QUIT) {
                exit(0);
            } else if (event.type == SDL_KEYDOWN && event.key.keysym.sym == SDLK_RETURN) {
                restart = true;
            }
        }
    }
    return restart;
}

bool draw_victory(SDL_Renderer *renderer, TTF_Font *font, int score, int highest) {
    //Draw black background
    SDL_SetRenderDrawColor(renderer, 0, 0, 0, 255);
    SDL_RenderClear(renderer);

    SDL_Color text_color = {255, 0, 0,255};

    char text[255];
    sprintf(text, "Victory! Your Score: %d", score);

    SDL_Surface *surface = TTF_RenderText_Solid(font, text, text_color);
    SDL_Texture *texture = SDL_CreateTextureFromSurface(renderer, surface);
    int texW = 0, texH = 0;
    SDL_QueryTexture(texture, NULL, NULL, &texW, &texH);
    SDL_Rect dstrect = {SCREEN_WIDTH / 2 - texW / 2, SCREEN_HEIGHT / 2 - texH / 2, texW, texH};

    SDL_RenderCopy(renderer, texture, NULL, &dstrect);

    char high[255];
    sprintf(high, "Highest Score: %d", highest);

    SDL_Surface *hsurface = TTF_RenderText_Solid(font, high, text_color);
    SDL_Texture *htexture = SDL_CreateTextureFromSurface(renderer, hsurface);
    int htexW = 0, htexH = 0;
    SDL_QueryTexture(htexture, NULL, NULL, &htexW, &htexH);
    SDL_Rect hrect = {SCREEN_WIDTH / 2 - htexW / 2, SCREEN_HEIGHT / 2 - htexH / 2 + 20, htexW, htexH};

    SDL_RenderCopy(renderer, htexture, NULL, &hrect);

    SDL_RenderPresent(renderer);

    SDL_DestroyTexture(texture);
    SDL_DestroyTexture(htexture);
    SDL_FreeSurface(surface);
    SDL_FreeSurface(hsurface);

    SDL_Event event;
    bool restart = false;
    while (!restart) {
        while (SDL_PollEvent(&event)) {
            if (event.type == SDL_QUIT) {
                exit(0);
            } else if (event.type == SDL_KEYDOWN && event.key.keysym.sym == SDLK_RETURN) {
                restart = true;
            }
        }
    }
    return restart;
}

void draw_background(SDL_Renderer *renderer, SDL_Texture *texture_background) {
    SDL_SetRenderDrawColor(renderer, 0, 0, 0, 255);
    SDL_RenderClear(renderer);
    SDL_Rect rect = {0, 0, SCREEN_WIDTH, SCREEN_HEIGHT};
    SDL_RenderCopy(renderer, texture_background, NULL, &rect);
}

void draw_score_name(SDL_Renderer *renderer, TTF_Font *font, int score, char *name)
{
    SDL_Color text_color = {255, 255, 255};

    char text[255];
    sprintf(text, "Player : %s Score : %d",name, score);

    SDL_Surface *surface = TTF_RenderText_Solid(font, text, text_color);
    SDL_Texture *texture = SDL_CreateTextureFromSurface(renderer, surface);
    int texW = 100, texH = 50;
    SDL_QueryTexture(texture, NULL, NULL, &texW, &texH);
    SDL_Rect dstrect = {0, 0, texW, texH};

    SDL_RenderCopy(renderer, texture, NULL, &dstrect);
    SDL_DestroyTexture(texture);
    SDL_FreeSurface(surface);
}

void draw_player(SDL_Renderer *renderer, Player player) {
    SDL_Rect player_rect;
    player_rect.x = player.x;
    player_rect.y = player.y;
    player_rect.w = 80;
    player_rect.h = 80;
    SDL_RenderCopy(renderer, player.texture_player, NULL, &player_rect);
}

int check_collision(SDL_Rect e, SDL_Rect b) {
    if (b.x >= e.x && b.x <= e.x + e.w && b.y >= e.y && b.y <= e.y + e.h) {
        return 0;
    } else {
        return 1;
    }
}

void draw_enemy(SDL_Renderer *renderer, Enemy *enemy_array) {
    for (int i = 0; i < enemy_count; i++) {

        if (enemy_array[i].available) {
            SDL_Rect enemy_rect;
            enemy_rect.x = enemy_array[i].x;
            enemy_rect.y = enemy_array[i].y;
            enemy_rect.w = 80;
            enemy_rect.h = 80;

            SDL_RenderCopy(renderer, enemy_array[i].texture_enemy, NULL, &enemy_rect);
        }

    }
}

void enemy_move(Enemy *enemy_array) {
    bool should_move = false;

    if (SDL_GetTicks() - last_tick_enemy_move > INTERVAL_MOVE_ENEMIES_MS) {
        should_move = true;
        last_tick_enemy_move = SDL_GetTicks();
    }

    if (should_move) {
        for (int i = 0; i < enemy_count; i++) {
            enemy_array[i].y += enemy_array[i].dir_y;
            enemy_array[i].x += enemy_array[i].dir_x;

            //Remove enemy if it goes out of screen
            if (enemy_array[i].y > SCREEN_HEIGHT || enemy_array[i].x > SCREEN_WIDTH || enemy_array[i].x < 0) {
                enemy_array[i].available = false;
                for (int j = i; j < enemy_count - 1; j++) {
                    enemy_array[j] = enemy_array[j + 1];
                }
                enemy_count--;
                i--;
            }
        }
    }
}

void add_enemy(Enemy *enemy_array, SDL_Texture **texture_enemy) {
    bool should_add = false;

    if(SDL_GetTicks() - last_tick_enemy_add > INTERVAL_ADD_ENEMIES_MS) {
        should_add = true;
        last_tick_enemy_add = SDL_GetTicks();
    }

    if (should_add) {
        if (enemy_count < MAX_ENEMIES) {
            Enemy new_enemy;

            new_enemy.available = true;
            new_enemy.enemy_index = rand() % 4;
            new_enemy.texture_enemy = texture_enemy[new_enemy.enemy_index];
            new_enemy.x = rand() % (SCREEN_WIDTH - 80);
            new_enemy.y = -80;
            new_enemy.dir_x = -5 + rand() % 10; // between -5 to 4
            new_enemy.dir_y = 5 + rand() % 10;
            
            enemy_array[enemy_count] = new_enemy;
            enemy_count++;
        }
    }
}

void draw_bullet_enemy(SDL_Renderer *renderer, BulletEnemy *bullet_enemy_array) {
    for (int i = 0; i < bullet_enemy_count; i++) {
        SDL_Rect bullet_enemy_rect;
        bullet_enemy_rect.x = bullet_enemy_array[i].x;
        bullet_enemy_rect.y = bullet_enemy_array[i].y;
        bullet_enemy_rect.w = 10;
        bullet_enemy_rect.h = 20;

        SDL_RenderCopy(renderer, bullet_enemy_array[i].texture_bullet_enemy, NULL, &bullet_enemy_rect);
    }
}

void draw_bullet(SDL_Renderer *renderer, Bullet *bullet_array) {
    for (int i = 0; i < bullet_count; i++) {
            SDL_Rect bullet_rect;
            bullet_rect.x = bullet_array[i].x;
            bullet_rect.y = bullet_array[i].y;
            bullet_rect.w = 10;
            bullet_rect.h = 20;

            SDL_RenderCopy(renderer, bullet_array[i].texture_bullet, NULL, &bullet_rect);

    }
}

void bullet_enemy_move(BulletEnemy *bullet_enemy_array, Player player, int *score, int *hp) {
    bool should_move = false;

    if(SDL_GetTicks() - last_tick_bullet_enemy_move > INTERVAL_MOVE_BULLETS_ENEMY_MS) {
        should_move = true;
        last_tick_bullet_enemy_move = SDL_GetTicks();
    }

    if(should_move) {
        for (int i = 0; i < bullet_enemy_count; i++) {
            bullet_enemy_array[i].y += bullet_enemy_array[i].speed;

            //Check for collision
            SDL_Rect bullet_enemy_rect = {bullet_enemy_array[i].x, bullet_enemy_array[i].y, 10, 20};
            SDL_Rect player_rect = {player.x, player.y, 80, 80};
            if (check_collision(player_rect, bullet_enemy_rect) == 0) {
                *hp -= 10;
                
                for (int j = i; j < bullet_enemy_count - 1; j++) {
                    bullet_enemy_array[j] = bullet_enemy_array[j + 1];
                }
                bullet_enemy_count--;
                i--;
            }

            //Remove bullet if it goes out of screen
            if(bullet_enemy_array[i].y > 620) { 
                for (int j = i; j < bullet_enemy_count - 1; j++) { 
                    //Make the next bullet the current bullet
                    bullet_enemy_array[j] = bullet_enemy_array[j + 1];
                }
                bullet_enemy_count--;
                i--;
            }
        }
    }
}

void add_bullet_enemy(BulletEnemy *bullet_enemy_array, Enemy *enemy_array, SDL_Texture *texture_bullet_enemy) {
    bool should_add = false;

    if(SDL_GetTicks() - last_tick_bullet_enemy_add > INTERVAL_ADD_BULLETS_ENEMY_MS) {
        should_add = true;
        last_tick_bullet_enemy_add = SDL_GetTicks();
    }

    if(should_add) {
        for (int i = 0; i < enemy_count; i++) {
            BulletEnemy new_bullet_enemy;
            if(bullet_enemy_count < MAX_BULLETS_ENEMY) {
                new_bullet_enemy.x = enemy_array[i].x + 35;
                new_bullet_enemy.y = enemy_array[i].y + 80 ;
                new_bullet_enemy.speed = 10;
            }
            new_bullet_enemy.texture_bullet_enemy = texture_bullet_enemy;

            bullet_enemy_array[bullet_enemy_count] = new_bullet_enemy;
            bullet_enemy_count++;
        }
    }
}

void bullet_move(Bullet *bullet_array, Enemy *enemy_array, int *score, Boss *boss) {
    bool should_move = false;

    if(SDL_GetTicks() - last_tick_bullet_move > INTERVAL_MOVE_BULLETS_MS) {
        should_move = true;
        last_tick_bullet_move = SDL_GetTicks();
    }

    if(should_move) {
        for (int i = 0; i < bullet_count; i++) {
            bullet_array[i].y += bullet_array[i].speed;

            //Check for collision with enemy
            SDL_Rect bullet_rect = {bullet_array[i].x, bullet_array[i].y, 10, 20};
            for (int j = 0; j < enemy_count; j++) {
                SDL_Rect enemy_rect = {enemy_array[j].x, enemy_array[j].y, 80, 80};
                if (check_collision(enemy_rect, bullet_rect) == 0) {

                    *score += 100;
                    //Remove enemy if hit
                    enemy_array[j].available = false;
                    for (int k = j; k < enemy_count - 1; k++) {
                        enemy_array[k] = enemy_array[k + 1];
                    }
                    enemy_count--;
                    j--;

                    //Remove bullet if hit
                    for (int k = i; k < bullet_count - 1; k++) {
                        bullet_array[k] = bullet_array[k + 1];
                    }
                    bullet_count--;
                    i--;
                }
            }

            //Check for collision with boss
            SDL_Rect boss_rect = {boss->x, boss->y, 160, 160};
            if(check_collision(boss_rect, bullet_rect) == 0) {
                boss->hp -= 5;
                *score += 50;
                for (int k = i; k < bullet_count - 1; k++) {
                    bullet_array[k] = bullet_array[k + 1];
                }
                bullet_count--;
                i--;
            }

            //Remove bullet if it goes out of screen
            if(bullet_array[i].y < -20) { 
                for (int j = i; j < bullet_count - 1; j++) { 
                    //Make the next bullet the current bullet
                    bullet_array[j] = bullet_array[j + 1];
                }
                bullet_count--;
                i--;
            }
        }
    }
}

void add_bullet(Bullet *bullet_array, Player player, SDL_Texture *texture_bullet) {
    bool should_add = false;

    if(SDL_GetTicks() - last_tick_bullet_add > INTERVAL_ADD_BULLETS_MS) {
        should_add = true;
        last_tick_bullet_add = SDL_GetTicks();
    }

    if(should_add) {
        if(bullet_count < MAX_BULLETS) {
            Bullet new_bullet;

            new_bullet.x = player.x + 35;
            new_bullet.y = player.y - 20;
            new_bullet.speed = -10;
            new_bullet.texture_bullet = texture_bullet;

            bullet_array[bullet_count] = new_bullet;
            bullet_count++;
        }
    }
}

void draw_hp(SDL_Renderer *renderer, int hp) {
    SDL_SetRenderDrawColor(renderer, 255, 0, 0, 255);
    SDL_Rect rect;
    rect.x = 10;
    rect.y = 50;
    rect.w = hp * 2;
    rect.h = 20;
    SDL_RenderFillRect(renderer, &rect);
}

void draw_boss(SDL_Renderer *renderer, Boss boss) {
        SDL_Rect boss_rect;
        boss_rect.x = boss.x;
        boss_rect.y = boss.y;
        boss_rect.w = 160;
        boss_rect.h = 160;

        SDL_RenderCopy(renderer, boss.texture_boss, NULL, &boss_rect);
}

void move_boss(Boss *boss, int score) {
    bool should_move = false;

    if(SDL_GetTicks() - last_tick_boss_move > INTERVAL_MOVE_BOSS_MS) {
        should_move = true;
        last_tick_boss_move = SDL_GetTicks();
    }

    if(should_move) {
        if(score > 500) {
            if(boss->y < 100) {
                boss->y += boss->dir_y;
            } else {
                boss->x += boss->dir_x;

                if(boss->x <= 0 || boss->x >= SCREEN_WIDTH - 160) {
                    boss->dir_x = -boss->dir_x;
                }
            }
        }
    }
}

void draw_bullet_boss(SDL_Renderer *renderer, BulletBoss *bullet_boss_array) {
    for (int i = 0; i < bullet_boss_count; i++) {
        SDL_Rect bullet_boss_rect;
        bullet_boss_rect.x = bullet_boss_array[i].x;
        bullet_boss_rect.y = bullet_boss_array[i].y;
        bullet_boss_rect.w = 10;
        bullet_boss_rect.h = 20;

        SDL_RenderCopy(renderer, bullet_boss_array[i].texture_bullet_boss, NULL, &bullet_boss_rect);
    }
}

void bullet_boss_move(BulletBoss *bullet_boss_array, Player player, int *hp) {
    bool should_move = false;

    if(SDL_GetTicks() - last_tick_bullet_boss_move > INTERVAL_MOVE_BULLETS_BOSS_MS) {
        should_move = true;
        last_tick_bullet_boss_move = SDL_GetTicks();
    }

    if(should_move) {
        for (int i = 0; i < bullet_boss_count; i++) {
            bullet_boss_array[i].y += bullet_boss_array[i].speed;

            //Check for collision
            SDL_Rect bullet_boss_rect = {bullet_boss_array[i].x, bullet_boss_array[i].y, 10, 20};
            SDL_Rect player_rect = {player.x, player.y, 80, 80};
            if (check_collision(player_rect, bullet_boss_rect) == 0) {
                *hp -= 10;
                
                for (int j = i; j < bullet_boss_count - 1; j++) {
                    bullet_boss_array[j] = bullet_boss_array[j + 1];
                }
                bullet_boss_count--;
                i--;
            }

            //Remove bullet if it goes out of screen
            if(bullet_boss_array[i].y > 620) { 
                for (int j = i; j < bullet_boss_count - 1; j++) { 
                    //Make the next bullet the current bullet
                    bullet_boss_array[j] = bullet_boss_array[j + 1];
                }
                bullet_boss_count--;
                i--;
            }
        }
    }
}

void add_bullet_boss(BulletBoss *bullet_boss_array, Boss boss, SDL_Texture *texture_bullet_boss) {
    bool should_add = false;

    if(SDL_GetTicks() - last_tick_bullet_boss_add > INTERVAL_ADD_BULLETS_BOSS_MS) {
        should_add = true;
        last_tick_bullet_boss_add = SDL_GetTicks();
    }

    if(should_add) {
        if(boss.y >= 100) {
            BulletBoss new_bullet_boss;
            if(bullet_boss_count < MAX_BULLETS_BOSS) {
                new_bullet_boss.x = boss.x + 75;
                new_bullet_boss.y = boss.y + 180;
                new_bullet_boss.speed = 15;
            }
            new_bullet_boss.texture_bullet_boss = texture_bullet_boss;

            bullet_boss_array[bullet_boss_count] = new_bullet_boss;
            bullet_boss_count++;
        }
    }
}

void draw_pill(SDL_Renderer *renderer, Pill *pill) {
    for (int i = 0; i < pill_count; i++) {
        if (pill[i].available) {
            SDL_Rect pill_rect;
            pill_rect.x = pill[i].x;
            pill_rect.y = pill[i].y;
            pill_rect.w = 50;
            pill_rect.h = 50;

            SDL_RenderCopy(renderer, pill[i].texture_pill, NULL, &pill_rect);
        }
    }
}

void add_pill(Pill *pill, SDL_Texture *texture_pill) {
    bool should_add = false;

    if(SDL_GetTicks() - last_tick_pill_add > INTERVAL_ADD_PILL_MS) {
        should_add = true;
        last_tick_pill_add = SDL_GetTicks();
    }

    if(should_add) {
        if(pill_count < MAX_PILLS) {
            for (int i = 0; i < MAX_PILLS; i++) {
                if (!pill[i].available) {
                    pill[i].available = true;
                    pill[i].x = rand() % (SCREEN_WIDTH - 80);
                    pill[i].y = 515;
                    pill[i].texture_pill = texture_pill;
                    pill_count++;
                    break;
                }
            }
        }
    }
}

void apply_pill(Player player, Pill *pill, int *hp) {
    SDL_Rect player_rect = {player.x, player.y, 80, 80};
    for (int i = 0; i < pill_count; i++) {
        if (pill[i].available) {
            SDL_Rect pill_rect = {pill[i].x, pill[i].y, 50, 50};
            if (check_collision(player_rect, pill_rect) == 0) {
                *hp += 10;
                pill[i].available = false;

                if (*hp > 100) {
                    *hp = 100;
                }

                pill_count--;
            }
        }
    }
}

int main(int argc, char *argv[]) {
    srand(time(NULL));

    bool quit = false;
    SDL_Event event;

    // Initialize SDL 
    SDL_Init(SDL_INIT_VIDEO);
    // Initialize SDL_ttf
    TTF_Init();
    // Initialize SDL_image 
    IMG_Init(IMG_INIT_PNG);

    TTF_Font *font = TTF_OpenFont("media/arial.ttf", 25);

    //Create window and renderer
    SDL_Window *window = SDL_CreateWindow(
    "Space Shooter",
    SDL_WINDOWPOS_CENTERED, SDL_WINDOWPOS_CENTERED, SCREEN_WIDTH, SCREEN_HEIGHT, 0);

	SDL_Renderer *renderer = SDL_CreateRenderer(window, -1, 0);

    //Load image
    SDL_Surface *img_background = IMG_Load("media/darkPurple.png");
    SDL_Surface *img_player = IMG_Load("media/playerShip1_blue.png");
    SDL_Surface *img_enemy1 = IMG_Load("media/enemyGreen1.png");
    SDL_Surface *img_enemy2 = IMG_Load("media/enemyGreen2.png");
    SDL_Surface *img_enemy3 = IMG_Load("media/enemyGreen3.png");
    SDL_Surface *img_enemy4 = IMG_Load("media/enemyGreen4.png");
    SDL_Surface *img_bullet_player = IMG_Load("media/laserBlue06.png");
    SDL_Surface *img_bullet_enemy = IMG_Load("media/laserGreen12.png");
    SDL_Surface *img_bullet_boss = IMG_Load("media/laserRed02.png");
    SDL_Surface *img_boss = IMG_Load("media/boss.png");
    SDL_Surface *img_pill = IMG_Load("media/pill_blue.png");

    //Convert surface to texture
    SDL_Texture *texture_background = SDL_CreateTextureFromSurface(renderer, img_background);
    SDL_Texture *texture_player = SDL_CreateTextureFromSurface(renderer, img_player);
    SDL_Texture *texture_enemy[4] = {
        SDL_CreateTextureFromSurface(renderer, img_enemy1),
        SDL_CreateTextureFromSurface(renderer, img_enemy2),
        SDL_CreateTextureFromSurface(renderer, img_enemy3),
        SDL_CreateTextureFromSurface(renderer, img_enemy4)
    };
    SDL_Texture *texture_bullet_player = SDL_CreateTextureFromSurface(renderer, img_bullet_player);
    SDL_Texture *texture_bullet_enemy = SDL_CreateTextureFromSurface(renderer, img_bullet_enemy);
    SDL_Texture *texture_bullet_boss = SDL_CreateTextureFromSurface(renderer, img_bullet_boss);
    SDL_Texture *texture_boss = SDL_CreateTextureFromSurface(renderer, img_boss);
    SDL_Texture *texture_pill = SDL_CreateTextureFromSurface(renderer, img_pill);

    //Free surface
    SDL_FreeSurface(img_background);
    SDL_FreeSurface(img_player);
    SDL_FreeSurface(img_enemy1);
    SDL_FreeSurface(img_enemy2);
    SDL_FreeSurface(img_enemy3);
    SDL_FreeSurface(img_enemy4);
    SDL_FreeSurface(img_bullet_player);
    SDL_FreeSurface(img_bullet_enemy);
    SDL_FreeSurface(img_bullet_boss);
    SDL_FreeSurface(img_boss);
    SDL_FreeSurface(img_pill);

    //Initialize score
    int score = 0;

    int highest = 0;
    char name[256];
    FILE *fptr;

    read_highest(fptr, &highest, name);

    //Initialize health point
    int hp = 100;

    //Initialize player
    Player player;
    player.texture_player = texture_player;
    player.x = SCREEN_WIDTH / 2 - 40;
    player.y = SCREEN_HEIGHT - 100;

    //Initialize bullets
    Bullet bullet_array[MAX_BULLETS];
    for (int i = 0; i < MAX_BULLETS; i++) {
        bullet_array[i].texture_bullet = texture_bullet_player;
    }

    //Initialize enemies
    Enemy enemy_array[MAX_ENEMIES] = {
        {.available = false},
        {.available = false},
        {.available = false},
        {.available = false} //Limit to 4 enemies
    };

    for (int i = 0; i < 4; i++) {
        enemy_array[i].dir_x = 10;
        enemy_array[i].dir_y = 10;
        enemy_array[i].texture_enemy = texture_enemy[i];
    }

    //Initialize enemies' bullets
    BulletEnemy bullet_enemy_array[MAX_BULLETS_ENEMY];
    for (int i = 0; i < MAX_BULLETS_ENEMY; i++) {
        bullet_enemy_array[i].texture_bullet_enemy = texture_bullet_enemy;
    }

    //Initialize boss' bullets
    BulletBoss bullet_boss_array[MAX_BULLETS_BOSS];
    for (int i = 0; i < MAX_BULLETS_BOSS; i++) {
        bullet_boss_array[i].texture_bullet_boss = texture_bullet_boss;
    }

    //Initialize boss
    Boss boss;
    boss.texture_boss = texture_boss;
    boss.x = SCREEN_WIDTH / 2 - 80;
    boss.y = -200;
    boss.dir_x = 15;
    boss.dir_y = 20;
    boss.hp = 100;

    //Initialize buff
    Pill pill[MAX_PILLS];
    for (int i = 0; i < MAX_PILLS; i++) {
        pill[i].available = false;
        pill[i].texture_pill = texture_pill;
    }


    while (!quit) {
        draw_menu(renderer, font, name);
        score = 0;
        hp = 100;
        bullet_count = 0;
        enemy_count = 0;
        bullet_enemy_count = 0;
        boss.hp = 100;
        bullet_boss_count = 0;
        pill_count = 0;

        player.x = SCREEN_WIDTH / 2 - 40;
        player.y = SCREEN_HEIGHT - 100;
        boss.x = SCREEN_WIDTH / 2 - 80;
        boss.y = -200;

        while (hp > 0 && boss.hp > 0) {
        // Handle events on queue 
        if (SDL_PollEvent(&event) != 0) {
            switch (event.type) {
                case SDL_QUIT:
                    quit = true;
                    break;
                case SDL_KEYDOWN:
                    switch (event.key.keysym.sym) {
                        case SDLK_LEFT:
                            if (player.x > 0) {
                                player.x -= 10;
                            }
                            break;
                        case SDLK_RIGHT:
                            if (player.x < SCREEN_WIDTH - 80) {
                                player.x += 10;
                            }
                            break;
                    }
                    break;
            }
        


        }

        add_bullet(bullet_array, player, texture_bullet_player);
        bullet_move(bullet_array, enemy_array, &score, &boss);

        add_enemy(enemy_array, texture_enemy);
        enemy_move(enemy_array);
        add_bullet_enemy(bullet_enemy_array, enemy_array, texture_bullet_enemy);
        bullet_enemy_move(bullet_enemy_array, player, &score, &hp);

        move_boss(&boss, score);
        add_bullet_boss(bullet_boss_array, boss, texture_bullet_boss);
        bullet_boss_move(bullet_boss_array, player, &hp);

        add_pill(pill, texture_pill);
        apply_pill(player, pill, &hp);

        draw_background(renderer, texture_background);
        draw_score_name(renderer, font, score, name);
        draw_hp(renderer, hp);
        draw_player(renderer, player);
        draw_bullet(renderer, bullet_array);
        draw_enemy(renderer, enemy_array);
        draw_bullet_enemy(renderer, bullet_enemy_array);
        draw_bullet_boss(renderer, bullet_boss_array);
        draw_pill(renderer, pill);
        draw_boss(renderer, boss);

        SDL_RenderPresent(renderer);

        if (hp == 0) {
            if (!draw_gameOver(renderer, font, score, highest)) {
                quit = true;
            }
        } else if (boss.hp == 0 && hp != 0) {
            if(score > highest) {
                highest = score;
                save_highest(fptr, highest, name);
            }
            if (!draw_victory(renderer, font, score, highest)) {
                quit = true;
            }
        }
        }

    }

    SDL_DestroyTexture(texture_background);
    SDL_DestroyTexture(texture_player);
    SDL_DestroyTexture(texture_bullet_player);
    SDL_DestroyTexture(texture_bullet_enemy);
    SDL_DestroyTexture(texture_bullet_boss);
    for (int i = 0; i < 4; i++) {
        SDL_DestroyTexture(texture_enemy[i]);
    }
    SDL_DestroyTexture(texture_boss);
    SDL_DestroyTexture(texture_pill);

    //Destroy window and renderer
    SDL_DestroyRenderer(renderer);
    SDL_DestroyWindow(window);

    //Quit SDL
    IMG_Quit();
    SDL_Quit();

    return 0;
}