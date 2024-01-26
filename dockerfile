# Utiliser l'image officielle PHP avec Apache
FROM php:7.4-apache

# Installer les extensions PHP nécessaires (ajoutez-en selon vos besoins)
RUN docker-php-ext-install pdo pdo_mysql

# Copier les sources de l'application dans le conteneur
COPY . /var/www/html/

# Donner la propriété au serveur web des fichiers
RUN chown -R www-data:www-data /var/www/html/

# Exposer le port 80 pour le trafic HTTP
EXPOSE 80

# Lancer Apache en arrière-plan
CMD ["apache2-foreground"]
