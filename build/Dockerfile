FROM php:5.5-cli

RUN apt-get update && apt-get install -y \
        libicu-dev \
        g++ \
        zlib1g-dev \
        wget \
    && docker-php-ext-install \
        mbstring \
        opcache \
        zip \
    && apt-get autoremove -y g++

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN pecl install -o -f xdebug
COPY xdebug.ini /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

RUN mkdir -p /var/log/betasmission
RUN mkdir -p /mnt/smb/Labox/
ADD fixtures /mnt/smb/Labox

VOLUME /home/pi/betasmission

RUN mkdir -p /home/pi/Downloads/Complete
RUN mkdir -p /home/pi/Downloads/Uncomplete

WORKDIR /home/pi/betasmission

EXPOSE 32775
EXPOSE 80

CMD tail -f /dev/null