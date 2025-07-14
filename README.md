# Introduction à Ansible

## Pourquoi Ansible ?
Ansible est un outil d'automatisation open§source conçu pour gérer et configurer des systèmes informatiques à distance. Il est largement utilisé pour l'administration système, la gestion des configuraitons, le déploiement d'applicaitons, ainsi que pour la gestion de l'infrastructure en tant que code (IaC : Infrastructure as Code).

- **Facilité d'utilisation** : Ansible utilise une synthaxe simple, déclarative et compréhensible/ La configuration est effectuée via des fichiers YAML (Yet Another Markup Language), qui sont faciles à écrire et à comprendre.
- **Agentless** : Contrairement à d'autres outils de gestion, Ansible n'a pas besoin d'un agent installé sur les systèmes distants. Il s'appuie sur SSH pour la communication avec les noeuds distants. 
- **Idempotence** : Ansible garantit que les configurations appliquées à un système ne le modifie pas s'il est déjà dans l'état désiré.
- **Extensible** : Vous pouvez étendre Ansible avec des modules personnalisés ou utiliser des rôles et collections existants.
- **Gestion de l'infrastructure** : Ansible permet de gérer les configurations, les déploiements et l'automatisation à échelle élevée. 

## Concepts fondamentaux d'Ansible
### Inventaire
L'inventaire est une liste de machines (hôtes) sur lesquelles Ansible va effectuer des actions. Il peut être statique ou dynamique. L'inventaire peut être sous forme de fichier texte ou d'un script générant la liste d'hôtes. <br>

*_Exemple d'un fichier d'inventaire statique_* : <br>
```
# /etc/ansible/hosts.ini

[webservers]
web1.example.com
web2.example.com

[dbservers]
db1.example.com
db2.example.com
```

### Playbooks
Un playbook Ansible est un fichier YAML qui contient une ou plusieurs "plays". Chaque "play" applique des tâches à un groupe d'hôtes. Un playbook peut inclure des rôles, des variables, des boucles, des conditions, etc.

*_ Exempled d'un simple playbook_*:
```
# playbook.yaml
---
- name : Installer et configurer Apache
  hosts : webservers
  become : True
  tasks : 
    - name: Installer Apache
      apt : 
        name: apache2
        state : present
    - name : Démarer Apache
      services : 
        name : apache2
        state : started
        enabled : yes
```

### Modules : 
Les modules sontdes unités de travail dans Ansible. Chaque module accomplit une tâche spécifique, comme installer un paquet, créer un fichier, redémarrer un service, etc. <br>
Quelques modules populaires:
- `apt` : Gestion des paquets sur les systèmes basés sur Debian
- `yum`: Gestion des paquetes sur les systèmes basés sur Red Hat
- `service`: Gestion des services (démarrer, arrêter, redémarrer)
- `copy` : Copier des fichiers d'une machine à une autre

### Rôles et Collections
Les rôles sont un moyen d'organiser les playbooks Ansible en réutilisant des configurations courantes. Un rôle peut inclure des tâches, des variables, des fichiers, des modèles, des handlers, etc. <br>

*_ Exemple de structure d'un rôle_* :
```
myrole\
    tasks\
        main.yaml
    files\
    templates\
    handlers\
    vars\
    defaults\
```

Les collections sont des ensembles de rôles et de modules qui peuvent être partagés et réutilisés dans Ansible.

### Variables et Templates
Les variables permettent de rendre un playbook dynamique en fonction de l'environnement.
Elles peuvent être définies dans le playboook, l'inventaire, ou des fichiers externes. <br>
Les templates sont des fichiers dynamiques générés par Ansible à l'aide de Jinja2, un moteur de templates.
Vous pouvez utiliser des variables dans vos fichiers de configuration pour personnaliser leur contenu.

# Cas Pratique
Utilisation de Ansible pour le déploiement d'un application web avec une architecture simple : 
un serveur web (Apache et une base de données MySQL).

## Architecture:
- Serveur Web (Apache) : Ce serveur héberge une application PHP et communique avec la base de données.
- Serveur DB (MySQL) : Ce serveur héberge une base de données MySQL.

## Etapes du déploiement

### Dépendances
![MariaDB](https://img.shields.io/badge/MariaDB%20v10.11.11-003545?style=for-the-badge&logo=mariadb&logoColor=white)
![Ansible](https://img.shields.io/badge/Ansible%20v2.14.18-000000?style=for-the-badge&logo=ansible&logoColor=white)
![PHP](https://img.shields.io/badge/PHP%20v8.2.28-777BB4?style=for-the-badge&logo=php&logoColor=white)

#### Étape 1 : Créer une VM sur GCP
- Créer une instance sur GCP :
- Allez sur la console GCP : [console GCP](https://console.cloud.google.com/).
- Créez un projet ou sélectionnez un projet existant.
- Dans le menu de gauche, allez dans Compute Engine > VM instances et cliquez sur Create Instance.
- Donnez un nom à votre VM (par exemple, my-web-server).
- Sélectionnez une image, comme Ubuntu 20.04 LTS (vous pouvez choisir n'importe quel OS basé sur Linux).
- Configurez les options de votre VM (type de machine, région, etc.).Ici nous avons :
    - Région : europe-west-9 (Paris)
    - Type de machine : e2-standard-4 (4 vCPU, 2 coeur(s), 16 Go de mémoire)
    - Accorder les accès externe et les requêtes HTTP & HTTPS
- Assurez-vous de permettre l'accès HTTP et HTTPS dans les options de firewall.
- Cliquez sur Create pour lancer la machine.
- Obtenez l'adresse IP externe de votre VM pour vous y connecter via SSH.
    - Ajout de la clé SSH de l'ordinateur courant pour VSCode :
      - `ls ~/.ssh` (Linux/MacOS) / `dir $HOME\.ssh` (Windows) (présence d’une clé SHH « id_rsa »)
      - `cat ~/.ssh/id_rsa.pub` (afficher la clé SSH à insérer sur GCP dans la section "Clé SSH")

#### Étape 2 : Configuration d'Ansible sur votre machine locale
1) Créer votre environnement virtuel et y installer Python 3.11:
```
python3 -m venv venv # Création de l'environnement virtuel nommé "venv"
sudo apt install python3.11-venv # Installation de python 3.11
source venv/bin/activate # Activer l'environnement virtuel
```
2) Installez Ansible sur la VM:
```
sudo apt update
sudo apt install ansible
```

3) Installer MariaDB sur la VM:
```
sudo apt install mariadb-server # Installer MariaDB
sudo systemctl start mariadb # Démarrer MariaDB
sudo systemctl enable mariadb
sudo mysql_secure_installation # Sécuriser l'installation
```

4) Installer PyMySQL:
```
sudo chown -R $USERNAME:$USERNAME /path/to/project/venv # Accorder les droits root
pip install PyMySQL
```

5) Installer PHP: `sudo apt install php libapache2-mod-php`

5) Définir un mot de passe utilisateur "root" pour MariaDB : `sudo mysql -u root`.
- Créer le mot de passe avec la requête suivante : `ALTER USER 'root'@'localhost' IDENTIFIED BY 'pwd';`
- Actualiser les privilèges : `FLUSH PRIVILEGES;`
- Quitter MariaDB : `EXIT;`

6) Préparez le fichier d’inventaire :
Ansible a besoin d'un fichier d'inventaire pour savoir sur quelles machines exécuter les commandes. Créez un fichier inventory qui contient l'adresse IP de votre VM GCP :
```
[webservers]
34.163.184.2  # Remplacez cette IP par l'IP externe de votre VM
```

#### Étape 3 : Préparer les Playbooks Ansible
Nous allons créer un playbook pour installer un serveur web Apache et une base de données MySQL. <br>

1) Créer le playbook deploy_app.yml :
2) Créez un fichier deploy_app.yml dans votre répertoire courant (cf. répertoire courant)

Ce playbook installe Apache et MariaDB, configure une base de données app_db et un utilisateur app_user sur la VM, et déploie un fichier PHP dans le répertoire web. <br>
Créer un fichier app.php pour tester l'application PHP. Ce fichier affichera la connexion à la base de données MySQL (cf. répertoire courant)

#### Étape 4 : Exécuter le playbook avec Ansible
Exécuter le playbook : <br>
Une fois votre fichier d'inventaire et votre playbook préparés, vous pouvez exécuter le playbook sur la VM GCP avec la commande suivante :
`ansible-playbook -i inventory deploy_app.yml --extra-vars "db_password=pwd"`

#### Etape 5 : Gérer les pare-feux
1) Vérifier que "app.php" est bien présent dans le répertoire "var/www/html" : `ls /var/www/html/app.php`
2) Vérifier que le fichier de configuration contient la directive suivante : `sudo nano /etc/apache2/apache2.conf`
```
<Directory /var/www/html>
    AllowOverride All
    Require all granted
</Directory>
```
Ajouter également cette section au fichier "000-default.conf" (si elle n'est pas présente) : `sudo nano /etc/apache2/sites-available/000-default.conf`
```
<Directory /var/www/html>
    Options Indexes FollowSymLinks MultiViews
    AllowOverride All
    Require all granted
</Directory>
```
3) Accorder le droit de lecture à Apache : `sudo chmod 644 /var/www/html/app.php && sudo a2enmod rewrite`
4) Affecter le fichier à "www-data" : `sudo chown www-data:www-data /var/www/html/app.php`
5) Autoriser les connexions HTTP : 
```
sudo apt install ufw
sudo ufw enable
sudo ufw allow 'Apache Full'
```
6) Autoriser le trafic sur différents ports :
```
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
sudo ufw reload
```

#### Étape 6 : Tester l'Application Web
Une fois que le playbook a terminé, vous pouvez ouvrir votre navigateur et accéder à l'adresse IP externe de votre VM (ex : http://34.163.184.2/app.php). <br>
Si tout est correctement configuré, vous devriez voir le message "Connected successfully to MySQL database!"

#### Conclusion 
Vous avez maintenant automatisé l'installation d'un serveur Apache et MariaDB sur une VM GCP à l'aide d'Ansible. Cela vous permet de gérer facilement la configuration de vos serveurs et de déployer rapidement des applications, ce qui est particulièrement utile pour les environnements de production ou de test.


## Bonnes pratiques
- **Utilisation des variables** : Utiliser des variables pour rendre vos playbooks plus flexibles et réutilisables.
- **Gestion des Secrets** : Utiliser Ansible Vault pour chiffrer des informations sensibles comme les mots de passe.
- **Idempotence** : Assurez§vous que vos playbooks sont idempotents, c'est-à-dire qu'ils peuvent être exécutés plusieurs fois sans changer l'état du système si ce dernier est déjà dans l'état désiré.
- **Tests** : Utilisez des outils comme "ansible§lint" pour vérifier la synthaxe et les bonnes pratiques dans bos playbooks.
- **Rôles et Collections** : Organisez vos playbooks en rôles pour les rendre réutilisables et faciles à maintenir.

## Conclusion générale
Ansible est un outil puissant pour automatiser la gestion des configurations et le déploiement d'applications. Grâce à sa synthaxe simple, ses modules variés et sa capacité à travailler sans agent, il est particulièrement adapté aux environnements DevOps et d'infrastructure cloud. 
En utilisant Ansible pour des tâches comme le déploiement d'applications, l'administration des systèmes ou le gestion de la configuration, vous pouvez améliorer l'efficacité de vos équipes et réduire les erreurs humaines.

## Etapes suivantes :
1. Continuer avec le développement de l'application
Développer la logique de votre application : Vous pouvez ajouter plus de fonctionnalités à votre application en interagissant avec la base de données (par exemple, ajouter, supprimer ou modifier des données). <br>
Tester les requêtes SQL : Assurez-vous que vos requêtes SQL dans le fichier PHP sont bien sécurisées pour éviter des vulnérabilités comme les injections SQL. Vous pouvez utiliser des requêtes préparées avec MySQLi pour cela. <br>

2. Gérer les erreurs
Activer le journal des erreurs : Assurez-vous que les erreurs PHP sont bien capturées et gérées, et que les utilisateurs finaux ne voient pas les erreurs techniques. Par exemple, utilisez try-catch pour les exceptions dans vos requêtes SQL.

3. Sécuriser l'application
Sécuriser la connexion à la base de données : Utilisez des mots de passe forts pour tous les utilisateurs MySQL et assurez-vous que votre fichier PHP ne contient pas de mots de passe en texte clair (vous pouvez utiliser des variables d'environnement ou des fichiers de configuration séparés). <br>
Activer HTTPS : Si ce n'est pas déjà fait, envisagez d'utiliser SSL/TLS pour sécuriser la connexion à votre site en HTTPS. Vous pouvez obtenir un certificat SSL gratuit avec Let's Encrypt.

4. Mettre en production
Optimiser les performances : Si votre application est prête à être mise en production, vous pouvez envisager d'optimiser la configuration Apache et PHP pour la performance, comme en activant OPcache dans PHP. <br>
Sécuriser l'accès : Ajoutez des règles de pare-feu et restreignez l'accès aux services non nécessaires pour réduire les risques de sécurité.

### Utilisation du repo
1) Installation de Git sur la VM :
- `sudo apt update` (mise à jour des packages)
- `sudo apt install git -y` (installation de Git)
2) Clôner le repo du projet : `git clone https://github.com/2FromField/Ansible_project.git`
3) Créer un environnement virtuel et l'activer : `python3 -m venv venv && soruce venv/bin/activate`
4) Installer les dépendances : `pip install §r requirements.txt`




