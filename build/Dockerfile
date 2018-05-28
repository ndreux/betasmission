FROM php:7.0-cli

RUN apt-get update && apt-get install -y \
        libicu-dev \
        g++ \
        zlib1g-dev \
        wget \
        git-all \
    && docker-php-ext-install \
        mbstring \
        opcache \
        zip \
    && apt-get autoremove -y g++

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN curl -LsS https://symfony.com/installer -o /usr/local/bin/symfony
RUN chmod a+x /usr/local/bin/symfony

RUN yes | pecl install xdebug \
    && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini

RUN mkdir -p /var/log/betasmission
RUN mkdir -p /mnt/smb/Labox/
ADD fixtures /mnt/smb/Labox

VOLUME /home/pi/betasmission

RUN mkdir -p /home/pi/Downloads/Complete
RUN mkdir -p /home/pi/Downloads/Uncomplete

WORKDIR /home/pi/betasmission

EXPOSE 32775
EXPOSE 9000
EXPOSE 80

CMD tail -f /dev/null