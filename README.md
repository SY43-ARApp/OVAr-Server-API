# OVAR Server API

**Base URl** : `http://chaelpixserver.ddns.net/apis/ovar/`


### LOGIN

`POST` **URL:**  

`http://chaelpixserver.ddns.net/apis/ovar/login.php`

**data:**
```
username=player1
hashed_password=P0$SV_E€
```

**Returns:**  
`user_id` (integer) or `FAIL`

ex : `http://chaelpixserver.ddns.net/apis/ovar/login.php?username=player1&password=P0$SV_E€`

---

### REGISTER

`POST` **URL:**  

`http://chaelpixserver.ddns.net/apis/ovar/register.php`

**data:**
```
username=newuser
hashed_password=P0$SV_E€
```

**Returns:**  
`REGISTERED` or `ERROR`

ex : `http://chaelpixserver.ddns.net/apis/ovar/register.php?username=newuser&password=P0$SV_E€`

---

### SEND SCORE

`POST` **URL:**  

`http://chaelpixserver.ddns.net/apis/ovar/send_score.php`

**data:**
```
user_id=1    // ID obtenu lors du login
score=2500
```

**Returns:**  
`SCORE_ADDED` or `FAIL`

ex : `http://chaelpixserver.ddns.net/apis/ovar/send_score.php?user_id=1&score=2500`

---

### GET SCORES

`GET` **URL:**  

`http://chaelpixserver.ddns.net/apis/ovar/get_scores.php`

**Optional parameters:**
```
limit=5 // Si rien, alors retourne tous les scores
```

**Returns:**  
JSON like:
```json
[
  {"username": "player1", "score": 4000},
  {"username": "player2", "score": 3500}
]
```

ex : `http://chaelpixserver.ddns.net/apis/ovar/get_scores.php?limit=5`