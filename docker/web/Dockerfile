FROM nginx:1.28

# Copy the custom nginx configuration file to the container
COPY docker/web/nginx.conf /etc/nginx/conf.d/default.conf

# Redirect nginx logs to stdout and stderr to allow Docker to capture them
RUN ln -sf /dev/stdout /var/log/nginx/access.log \
    && ln -sf /dev/stderr /var/log/nginx/error.log