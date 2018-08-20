#FROM circleci/php:5.6.37-node-browsers
FROM circleci/php:7.1-fpm-node-browsers

ADD . /home/circleci/project
WORKDIR /home/circleci/project

# PHP 5.6
# RUN sudo docker-php-ext-install mysql

# PHP 7.1
RUN sudo docker-php-ext-install mysqli

RUN sudo apt-get update
RUN sudo apt-get install -y vim subversion mysql-client

RUN sudo touch /usr/local/etc/php/php.ini
RUN echo "memory_limit = -1" | sudo tee -a /usr/local/etc/php/php.ini
RUN echo "display_startup_errors = On" | sudo tee -a /usr/local/etc/php/php.ini
RUN echo "xdebug.force_display_errors = On" | sudo tee -a /usr/local/etc/php/php.ini
RUN echo "error_reporting = E_ALL ^ E_DEPRECATED" | sudo tee -a /usr/local/etc/php/php.ini
RUN echo "xdebug.force_error_reporting = E_ALL ^ E_DEPRECATED" | sudo tee -a /usr/local/etc/php/php.ini
RUN echo "detect_unicode = Off" | sudo tee -a /usr/local/etc/php/php.ini

CMD ["bash", "bin/docker-entrypoint-wp.sh"]