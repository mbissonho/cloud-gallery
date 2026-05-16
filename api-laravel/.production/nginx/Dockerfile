FROM nginx:stable-alpine

ARG LOCAL_ENV="false"

RUN rm /etc/nginx/conf.d/default.conf
COPY ./.production/nginx/nginx.conf /etc/nginx/conf.d/nginx.conf
COPY ./.production/nginx/nginx.local.conf /etc/nginx/conf.d/nginx.local.conf

RUN if [ "$LOCAL_ENV" = "false" ]; then \
      mv /etc/nginx/conf.d/nginx.conf /etc/nginx/conf.d/default.conf; \
      rm /etc/nginx/conf.d/nginx.local.conf; \
    else \
      mv /etc/nginx/conf.d/nginx.local.conf /etc/nginx/conf.d/default.conf; \
      rm /etc/nginx/conf.d/nginx.conf; \
    fi

COPY public /var/www/html/public

EXPOSE 80

CMD ["nginx", "-g", "daemon off;"]
