# API de Métriques pour Firecracker VMs

Cette API permet de collecter et de stocker les métriques d'utilisation des machines virtuelles Firecracker.

## Configuration

### Variables d'environnement
Créez un fichier `.env` à la racine du projet avec les variables suivantes :

```env
DB_USERNAME=kairoskm
DB_PASSWORD=kairoskm
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=firecracker
```

### Installation

1. Créer un environnement virtuel Python :
```bash
python -m venv venv
source venv/bin/activate  # Linux/Mac
```

2. Installer les dépendances :
```bash
pip install -r requirements.txt
```

## Utilisation de l'API

### Endpoint pour les métriques

`POST /api/cpu_metrics`

Permet de mettre à jour les métriques d'une VM.

#### Paramètres de la requête
```json
{
    "user_id": "integer",
    "vm_id": "integer",
    "cpu_usage": "float",
    "memory_usage": "float",
    "disk_usage": "float"
}
```

#### Réponses

- Succès (200)
```json
{
    "message": "Métriques enregistrées avec succès"
}
```

- Erreur VM non trouvée (404)
```json
{
    "detail": "Machine virtuelle introuvable"
}
```

- Erreur de validation (400)
```json
{
    "detail": "Les paramètres sont invalides"
}
```

### Démarrage du serveur

Pour lancer l'API :
```bash
uvicorn main:app --host 0.0.0.0 --port 5001 --reload
```

## Structure de la base de données

Table `virtual_machines` :
- `id` (Integer, Primary Key)
- `user_id` (Integer)
- `cpu_usage_percent` (Float)
- `memory_usage_mib` (Float)
- `disk_usage_bytes` (Float)

## Documentation API

La documentation interactive de l'API est disponible à :
- Swagger UI : `http://localhost:5001/docs`
- ReDoc : `http://localhost:5001/redoc`
