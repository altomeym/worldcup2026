# 🔌 Public JSON API

World Cup 2026 Companion exposes a small, **read-only JSON API**. It is written in
PHP, but **any language can consume it** (Python, JavaScript, Java, Go, Rust, C#, …)
because it speaks plain HTTP + JSON.

- **No API key required.** Data comes from the public-domain
  [openfootball](https://github.com/openfootball) dataset and is cached on the server.
- **CORS is open** (`Access-Control-Allow-Origin: *`), so you can call it directly
  from a browser / front-end app.
- Responses are cached ~60s (`Cache-Control: public, max-age=60`).

> ⚠️ Unofficial fan project — not affiliated with FIFA. Data is "live-style", best-effort.

---

## Base URL

| Environment | Base URL |
|---|---|
| Live demo | `https://wcup2026.org` |
| Docker (this repo) | `http://localhost:8080` |
| XAMPP subfolder | `http://localhost/worldcup2026` |

All endpoints below are relative to the base URL, e.g.
`https://wcup2026.org/api/data.php?action=today`.

---

## Endpoint: `GET /api/data.php`

Select what you want with the `action` query parameter.

| `action` | Extra params | Returns |
|---|---|---|
| `today` | — | Matches scheduled for today |
| `live` | — | Matches currently in progress |
| `upcoming` | `limit` (default 10) | Next upcoming matches |
| `results` | `limit` (default 10) | Most recent finished matches |
| `all` | — | Every match in the tournament |
| `match` | `id` (required) | A single match by its id |
| `group` | `g` (e.g. `Group A`) | Standings table for one group |
| `standings` | — | Standings tables for all groups |

### Common response envelope

```json
{
  "ok": true,
  "action": "today",
  "updated": 1781200800,
  "matches": [ /* array of Match objects */ ]
}
```

On error: `{ "ok": false, "error": "not_found" }` (or `"unknown_action"`).

### Match object

```json
{
  "id": 0,
  "round": "Matchday 1",
  "group": "Group A",
  "team1": "Mexico",
  "team2": "South Africa",
  "team1_ar": "المكسيك",
  "team2_ar": "جنوب أفريقيا",
  "flag1": "https://flagcdn.com/w80/mx.png",
  "flag2": "https://flagcdn.com/w80/za.png",
  "status": "scheduled",
  "score": null,
  "live_minute": null,
  "date": "2026-06-11",
  "time": "11:00 PM",
  "datetime": 1781200800,
  "ground": "Mexico City"
}
```

| Field | Type | Notes |
|---|---|---|
| `id` | int | Stable index; use it with `action=match&id=` |
| `team1` / `team2` | string | English team names (openfootball spelling) |
| `team1_ar` / `team2_ar` | string | Arabic team names |
| `flag1` / `flag2` | string | URL to the flag image |
| `status` | string | `scheduled`, `live`, or `finished` |
| `score` | array/null | `[home, away]` once played, else `null` |
| `live_minute` | int/null | Current minute when `status` is `live` |
| `datetime` | int | Unix timestamp (UTC) |

### Standings row (`group` / `standings`)

```json
{ "team": "Mexico", "p": 3, "w": 2, "d": 1, "l": 0, "gf": 5, "ga": 2, "gd": 3, "pts": 7 }
```

`p`=played, `w`=won, `d`=draw, `l`=lost, `gf`=goals for, `ga`=goals against,
`gd`=goal difference, `pts`=points (win 3, draw 1). Sorted by pts → gd → gf → name.

`action=standings` returns an object keyed by group name:

```json
{ "ok": true, "standings": { "Group A": [ /* rows */ ], "Group B": [ /* rows */ ] } }
```

---

## Static data files (no server needed)

These plain JSON files live in [`/data`](../data) and can be downloaded or imported directly:

| File | Contents |
|---|---|
| `data/worldcup_fallback.json` | Full fixture list (rounds, dates, teams, groups, venues) |
| `data/rankings.json` | FIFA-style ranking per team (`"Team": rank`) |
| `data/referees.json` | Referees dataset |

---

## Examples

### cURL
```bash
curl "https://wcup2026.org/api/data.php?action=today"
curl "https://wcup2026.org/api/data.php?action=standings"
curl "https://wcup2026.org/api/data.php?action=match&id=0"
```

### Python
```python
import requests

BASE = "https://wcup2026.org/api/data.php"
today = requests.get(BASE, params={"action": "today"}).json()
for m in today["matches"]:
    print(f'{m["date"]} {m["time"]}  {m["team1"]} vs {m["team2"]}')
```

### JavaScript (browser or Node 18+)
```javascript
const BASE = "https://wcup2026.org/api/data.php";
const res  = await fetch(`${BASE}?action=upcoming&limit=5`);
const data = await res.json();
data.matches.forEach(m => console.log(`${m.team1} vs ${m.team2}`));
```

### PHP
```php
$data = json_decode(file_get_contents(
    "https://wcup2026.org/api/data.php?action=standings"), true);
print_r($data["standings"]["Group A"]);
```

---

## Notes & fair use

- Please cache on your side and avoid hammering the demo server.
- For heavy use, **self-host** (see the [README](../README.md) — Docker is one command)
  and point your code at your own instance.
