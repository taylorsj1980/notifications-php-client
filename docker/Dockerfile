FROM php:5.6-cli

ARG APT_HTTP_PROXY

ENV DEBIAN_FRONTEND=noninteractive

RUN \
	echo "Install base packages" \
	&& ([ -z "$APT_HTTP_PROXY" ] || echo "Acquire::http::Proxy \"${APT_HTTP_PROXY}\";\n" > /etc/apt/apt.conf.d/99HttpProxy) \
	&& apt-get update \
	&& apt-get install -y --no-install-recommends \
		zlib1g-dev \
		make \
	&& docker-php-ext-install zip \
	&& curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
	&& echo "Clean up" \
	&& rm -rf /var/lib/apt/lists/* /tmp/*

WORKDIR /var/project
