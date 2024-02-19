ARG PHP_VERSION
FROM php:${PHP_VERSION}

# Instala as extensões
RUN apt-get update && apt-get install -y \
    sqlite3 libsqlite3-dev git unzip
    
RUN docker-php-ext-install pdo pdo_sqlite

# Instala o Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Habilita o módulo mod_rewrite do Apache
RUN a2enmod rewrite

# Copia os arquivos do projeto para o contêiner
COPY . /var/www/html/

# Define o diretório de trabalho
WORKDIR /var/www/html/

# Instala as dependências do projeto
RUN composer install

# Altera o proprietário e as permissões dos arquivos do projeto
RUN chown -R www-data:www-data /var/www/html
RUN chmod -R 755 /var/www/html

# Inicia o servidor Apache
CMD ["apache2-foreground"]
