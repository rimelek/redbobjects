FROM itsziget/php:7.4-fpm

ENV XDEBUG_VERSION="3.1.3"
ENV XDEBUG_REMOTE_PORT="9000"
ENV XDEBUG_REMOTE_CONNECT_BACK="1"
ENV XDEBUG_IDEKEY="PHPSTORM"
ENV XDEBUG_AUTOSTART="off"
ENV XDEBUG_INI="/usr/local/etc/php/conf.d/xdebug.ini"

# composer installer: https://getcomposer.org/doc/faqs/how-to-install-composer-programmatically.md
ENV COMPOSER_VERSION="2.2.6"
ENV COMPOSER_INSTALLER_VERSION="76a7060ccb93902cd7576b67264ad91c8a2700e2"

ENV PHPCS_VERSION="3.6.2"
ENV PHPCS_RELEASE_URL="https://github.com/squizlabs/PHP_CodeSniffer/releases/download/$PHPCS_VERSION/phpcs.phar"
ENV PHPCS_BIN="/usr/local/bin/phpcs"


RUN rm -rf /var/lib/apt/lists/* \
 && apt-get update && apt-get install -y --no-install-recommends git zlib1g-dev openssh-client \
 && docker-php-ext-install zip \
 && apt-get remove --purge -y zlib1g-dev \
 && php -r "readfile('https://raw.githubusercontent.com/composer/getcomposer.org/${COMPOSER_INSTALLER_VERSION}/web/installer');" \
    | php -- --install-dir=/usr/bin/ --filename=composer --version=${COMPOSER_VERSION} \
 && if [ -n "${XDEBUG_VERSION}" ]; then \
      pecl install xdebug-${XDEBUG_VERSION} \
        && echo "zend_extension=$(find /usr/local/lib/php/extensions/ -name xdebug.so)" > ${XDEBUG_INI}  \
        && echo "xdebug.remote_enable=on" >> ${XDEBUG_INI} \
        && echo "xdebug.remote_autostart=\${XDEBUG_AUTOSTART}" >> ${XDEBUG_INI} \
        && echo "xdebug.remote_port=\${XDEBUG_REMOTE_PORT}" >> ${XDEBUG_INI} \
        && echo "xdebug.remote_connect_back=\${XDEBUG_REMOTE_CONNECT_BACK}" >> ${XDEBUG_INI} \
        && echo "xdebug.idekey=\${XDEBUG_IDEKEY}" >> ${XDEBUG_INI}; \
    fi \
  && curl -L "${PHPCS_RELEASE_URL}" > "${PHPCS_BIN}" \
  && chmod +x "${PHPCS_BIN}"
