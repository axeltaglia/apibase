FROM alpine:3.8
ARG upstream_container

RUN apk add --update nginx
RUN rm -rf /var/cache/apk/* && rm -rf /tmp/* && rm -rf /etc/nginx/conf.d/*

ADD nginx.conf /etc/nginx/
ADD symfony.conf /etc/nginx/conf.d/

RUN echo "upstream php-upstream { server $upstream_container:9001; }" > /etc/nginx/conf.d/upstream.conf

RUN adduser -D -g '' -G www-data www-data

CMD ["nginx"]

EXPOSE 80
EXPOSE 443
