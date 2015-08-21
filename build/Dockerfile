FROM php:5.5-cli

RUN useradd -m pi
RUN echo pi:pwd | chpasswd

RUN mkdir -p /var/log/betasmission

COPY php.ini /usr/local/etc/php/php.ini

VOLUME /home/pi/betasmission
VOLUME /mnt/smb/Labox/

EXPOSE 2222

CMD [ "tail", "-f", "/dev/null"]
#CMD [ "/home/pi/betasmission/run", "subtitle"]

