# Projet Laravel 10 avec React 18

Ce projet est une application web utilisant Laravel 10 pour le backend et React 18 pour le frontend. Il permet de recevoir des fichiers de type PDF, DOCX ou TXT et de les analyser en utilisant un assistant basé sur GPT-4.

## Article complet sur Medium
- <a href="https://medium.com/@r.alexandre/laravel-10-react-18-openai-envoyer-un-fichier-pdf-et-poser-votre-question-b910e4e819cd" targe="_blank">https://medium.com/@r.alexandre/laravel-10-react-18-openai-envoyer-un-fichier-pdf-et-poser-votre-question-b910e4e819cd</a>

## Prérequis

Avant de commencer, assurez-vous d'avoir installé les éléments suivants :

- [PHP 8.1+](https://www.php.net/downloads)
- [Composer](https://getcomposer.org/download/)
- [Node.js 20+ et npm](https://nodejs.org/en/download/)

## Installation

1. Clonez le dépôt du projet :

    ```bash
    git clone https://github.com/puretalent-biz/ai-pdf-analyzer.git
    cd ai-pdf-analyzer
    ```

2. Installez les dépendances PHP avec Composer :

    ```bash
    composer install
    composer update
    ```

3. Installez les dépendances JavaScript avec npm (seulement en local) :

    ```bash
    npm install
    ```

    Attention: node >= 20 doit être installé

4. Copiez le fichier `.env.example` en `.env` et configurez vos variables d'environnement :

    ```bash
    cp .env.example .env
    ```

    Modifiez le fichier `.env` pour configurer votre clé API.

    ```bash
    php artisan storage:link
    php artisan key:generate
    php artisan optimize
    ```

5. Donner les droits nécessaires sur un serveur Linux

    ```bash
    sudo chown -R $USER:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    ```


## Mise en route en local

1. Démarrez le serveur de développement Laravel :

    ```bash
    php artisan serve
    ```

    Le serveur sera accessible à l'adresse [http://localhost:8000](http://localhost:8000).

2. Démarrez le serveur de développement React :

    ```bash
    npm run dev
    ```

## Build de production

1. Pour créer un build de production de l'application React, exécutez la commande suivante :

    ```bash
    npm run build
    ```

Les fichiers de build seront générés dans le répertoire public/js de votre projet Laravel.

##  Déploiement
Pour déployer votre application sur un serveur de production, suivez ces étapes :

- Assurez-vous que les dépendances sont installées.
- Créez un build de production de l'application React.
- Configurez votre serveur web (Apache, Nginx, etc.) pour pointer vers le répertoire public de votre projet Laravel.
- Assurez-vous que les permissions des répertoires storage et bootstrap/cache sont correctement définies.

## Utilisation
Pour utiliser l'application, accédez à l'interface web et téléchargez un fichier PDF, DOCX ou TXT. L'assistant analysera le contenu du fichier et fournira une réponse basée sur le modèle GPT-4.

## Contribuer
Les contributions sont les bienvenues ! Si vous souhaitez contribuer, veuillez suivre ces étapes :

## Forkez le dépôt.
Créez une branche pour votre fonctionnalité (git checkout -b feature/ma-fonctionnalité).
Commitez vos modifications (git commit -am 'Ajout de ma fonctionnalité').
Poussez votre branche (git push origin feature/ma-fonctionnalité).
Ouvrez une Pull Request.

## Licence
Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

## Remerciements
Merci à tous les contributeurs de ce projet. Si vous avez des questions ou des suggestions, n'hésitez pas à ouvrir une issue ou à me contacter.