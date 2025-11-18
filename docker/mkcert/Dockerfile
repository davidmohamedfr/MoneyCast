FROM alpine:latest

# Install dependencies and mkcert
RUN apk add --no-cache \
    ca-certificates \
    curl \
    && rm -rf /var/cache/apk/* \
    && curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64" \
    && chmod +x mkcert-v*-linux-amd64 \
    && mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert

# Create directory for certificates
RUN mkdir -p /certs

# Copy certificate generation script
COPY generate-certs.sh /generate-certs.sh
RUN chmod +x /generate-certs.sh

# Set entrypoint to generate certificates
ENTRYPOINT ["/generate-certs.sh"]