# Fail2Ban - Projet BTS2

## Objectif
Bloquer une IP apres **5 mauvais mots de passe** pendant **1 heure**.

## Prerequis
- Fail2Ban installe sur la machine
- service fail2ban actif

## Configuration projet
Le projet journalise les echecs de connexion dans:
- `storage/logs/security.log`

Format utilise:
```txt
2026-04-03T13:40:00+00:00 AUTH_FAIL ip=1.2.3.4 login=user@example.test reason=invalid_credentials uri=/login
```

Le script:
- `scripts/setup-fail2ban.sh`

Lancement:
```bash
sudo ./scripts/setup-fail2ban.sh
```

cree:
- `/etc/fail2ban/filter.d/projet-bts2-auth.conf`
- `/etc/fail2ban/jail.d/projet-bts2-auth.local`

avec:
- `maxretry = 5`
- `bantime = 1h`
- `findtime = 10m`

et active le test local (`ignoreself = false`, `ignoreip =`).
