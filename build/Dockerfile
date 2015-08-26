FROM php:5.5-cli

RUN pecl install xdebug

RUN mkdir -p /var/log/betasmission
RUN mkdir -p /mnt/smb/Labox/
ADD fixtures /mnt/smb/Labox
COPY php.ini /usr/local/etc/php/php.ini

VOLUME /home/pi/betasmission

EXPOSE 32775
EXPOSE 80

CMD [ "tail", "-f", "/dev/null"]

