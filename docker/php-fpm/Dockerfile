#
#--------------------------------------------------------------------------
# Image Setup
#--------------------------------------------------------------------------
#
# To edit the 'php-fpm' base Image, visit its repository on Github
#    https://github.com/LaraDock/php-fpm
#
# To change its version, see the available Tags on the Docker Hub:
#    https://hub.docker.com/r/laradock/php-fpm/tags/
#

#
# SADLY I'm downgrading this from PHP7 to 5.6, to make Zookeeper extension work properly
# There is commented out code below that attempts to make it go under 7, but there is
# no official package.
#FROM laradock/php-fpm:7.0--1.2
FROM laradock/php-fpm:5.6--1.2

#
#--------------------------------------------------------------------------
# Mandatory Software's Installation
#--------------------------------------------------------------------------
#
# Mandatory Software's such as ("mcrypt", "pdo_mysql", "libssl-dev", ....)
# are installed on the base image 'laradock/php-fpm' image. If you want
# to add more Software's or remove existing one, you need to edit the
# base image (https://github.com/LaraDock/php-fpm).
#

# WE NEED ZOOKEEPER

# Basics :)
RUN apt-get update && apt-get -y install wget

# First, we need the Zookeeper headers and library.
RUN cd && \
    wget ftp://mirror.csclub.uwaterloo.ca/apache/zookeeper/zookeeper-3.4.10/zookeeper-3.4.10.tar.gz && \
    tar xzpf zookeeper-3.4.10.tar.gz && \
    cd zookeeper-3.4.10/src/c && \
    ./configure --prefix=/usr/ && \
    make && \
    make install

# This should work under php5.6 (easy)
# Install the PHP zookeeper extention (for Kafka)
RUN pecl install zookeeper
RUN docker-php-ext-enable zookeeper

## This is the attempt at php7 zookeeper
## http://blog.programster.org/install-zookeeper-php-extension/
## Need Git
#RUN apt-get -y install git
## Zookeeper PHP extension
#RUN cd && \
#    git clone https://github.com/andreiz/php-zookeeper.git && \
#    cd php-zookeeper && \
#    phpize && \
#    ./configure && \
#    make && \

#
#--------------------------------------------------------------------------
# Optional Software's Installation
#--------------------------------------------------------------------------
#
# Optional Software's will only be installed if you set them to `true`
# in the `docker-compose.yml` before the build.
# Example:
#   - INSTALL_ZIP_ARCHIVE=true
#   - ...
#
ARG INSTALL_XDEBUG=true
RUN if [ ${INSTALL_XDEBUG} = true ]; then \
    # Install the xdebug extension
    pecl install xdebug && \
    docker-php-ext-enable xdebug \
;fi


#
#--------------------------------------------------------------------------
# Final Touch
#--------------------------------------------------------------------------
#

ADD ./laravel.ini /usr/local/etc/php/conf.d
ADD ./laravel.pool.conf /usr/local/etc/php-fpm.d/

RUN rm -r /var/lib/apt/lists/*

RUN usermod -u 1000 www-data

WORKDIR /var/www

CMD ["php-fpm"]

EXPOSE 9000
