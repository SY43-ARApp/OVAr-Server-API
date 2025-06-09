# OVAR Server API

**Base URL** : `http://chaelpixserver.ddns.net/apis/ovar/`

---

## Endpoints

### LOGIN

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/login.php?uuid=<uuid>`

**Returns:**  
`GOOD` or `UNKNOWN_UUID`

---

### REGISTER

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/register.php?uuid=<uuid>&username=<username>`

**Returns:**  
`REGISTERED:<uuid>` or `ERROR_UPDATE` or `ERROR_INSERT` or `ERROR_MISSING_PARAMS`

---

### CHECK USERNAME

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/check_username.php?username=<username>`

**Returns:**  
JSON: `{ "available": true }` or `{ "available": false, "message": "Username is taken" }`

---

### SEND SCORE

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/send_score.php?user_id=<uuid>&score=<score>&arrows_thrown=<int>&planets_hit=<int>&levels_passed=<int>`

**Returns:**  
`SCORE_ADDED` or `FAIL`

---

### GET SCORES

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/get_scores.php?limit=<n>`

**Returns:**  
JSON array: `[ { "username": "player1", "score": 4000 }, ... ]`

---

### GET USER MONEY

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/get_user_money.php?uuid=<uuid>`

**Returns:**  
JSON: `{ "money": <int> }` or error JSON

---

### GET USER ROLE

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/get_user_role.php?uuid=<uuid>`

**Returns:**  
JSON: `{ "role": <String> }` or error JSON

---


### GET USER SKINS

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/get_user_skins.php?uuid=<uuid>`

**Returns:**  
JSON array: `[ { "skinId": <int>, "type": <int> }, ... ]`

---

### GET ALL SKINS

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/get_all_skins.php`

**Returns:**  
JSON array: `[ { "id": <int>, "price": <int>, "minimalScore": <int>, "type": <int> }, ... ]`

---

### BUY SKIN

`GET` **URL:**  
`http://chaelpixserver.ddns.net/apis/ovar/buy_skin.php?uuid=<uuid>&skin_id=<skin_id>`

**Returns:**  
JSON: 
- `{ "success": true, "message": "SKIN_PURCHASED" }`
- `{ "success": false, "error": "MISSING_UUID" | "INVALID_SKIN_ID" | "USER_NOT_FOUND" | "SKIN_NOT_FOUND" | "ALREADY_OWNED" | "INSUFFICIENT_FUNDS" | "UPDATE_MONEY_FAIL" | "ADD_SKIN_FAIL" }`

---

### FILES

- `login.php` : Authentification utilisateur par uuid
- `register.php` : Création ou mise à jour d'un utilisateur
- `check_username.php` : Vérification de la disponibilité d'un pseudo
- `send_score.php` : Envoi d'un score utilisateur
- `get_scores.php` : Récupération des scores globaux
- `get_user_money.php` : Récupération de l'argent d'un utilisateur
- `get_user_skins.php` : Récupération des skins possédés par un utilisateur
- `get_user_role.php` : Récupération du rôle d'un utilisateur
- `get_all_skins.php` : Liste de tous les skins disponibles
- `buy_skin.php` : Achat d'un skin (voir détails ci-dessus)
- `update_server.php` : Script de mise à jour serveur

---

## Récapitulatif des Entrées et Sorties

| Endpoint                                 | Méthode | Entrées (paramètres)                                   | Sorties (réponse)                                                                                       |
|------------------------------------------|---------|-------------------------------------------------------|---------------------------------------------------------------------------------------------------------|
| `/login.php`                             | GET     | uuid                                                  | `GOOD` ou `UNKNOWN_UUID`                                                                                |
| `/register.php`                          | GET     | uuid, username                                        | `REGISTERED:<uuid>`, `ERROR_UPDATE`, `ERROR_INSERT`, `ERROR_MISSING_PARAMS`                             |
| `/check_username.php`                    | GET     | username                                              | `{ "available": true }` ou `{ "available": false, "message": "Username is taken" }`                |
| `/send_score.php`                        | GET     | user_id/uuid, score, arrows_thrown, planets_hit, levels_passed | `SCORE_ADDED` ou `FAIL`                                                                                 |
| `/get_scores.php`                        | GET     | limit (optionnel)                                     | `[ { "username": "player1", "score": 4000 }, ... ]`                                                |
| `/get_user_money.php`                    | GET     | uuid                                                  | `{ "money": <int> }` ou erreur JSON                                                                   |
| `/get_user_role.php`                     | GET     | uuid                                                  | `{ "role": <String> }` ou erreur JSON                                                                  |
| `/get_user_skins.php`                    | GET     | uuid                                                  | `[ { "skinId": <int>, "type": <int> }, ... ]`                                                        |
| `/get_all_skins.php`                     | GET     |                                                       | `[ { "id": <int>, "price": <int>, "minimalScore": <int>, "type": <int> }, ... ]`                  |
| `/buy_skin.php`                          | GET     | uuid, skin_id                                         | `{ "success": true, "message": "SKIN_PURCHASED" }` ou `{ "success": false, "error": ... }`       |

---

Pour toute question, contactez l'équipe OVAR.