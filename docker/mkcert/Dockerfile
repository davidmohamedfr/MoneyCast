FROM alpine:latest

# Install dependencies and mkcert
# Pin to specific version for security and reproducibility
ARG MKCERT_VERSION=v1.4.4
ARG MKCERT_SHA256=6d31c65b03972c6dc4a14ab429f2928300518b26503f58723e532d1b0a3bbb52

RUN apk add --no-cache \
    ca-certificates \
    curl \
    && rm -rf /var/cache/apk/* \
    && curl -JLO "https://dl.filippo.io/mkcert/${MKCERT_VERSION}?for=linux/amd64" \
    && echo "${MKCERT_SHA256}  mkcert-${MKCERT_VERSION}-linux-amd64" | sha256sum -c - \
    && chmod +x mkcert-${MKCERT_VERSION}-linux-amd64 \
    && mv mkcert-${MKCERT_VERSION}-linux-amd64 /usr/local/bin/mkcert

# Create directory for certificates
RUN mkdir -p /certs

# Copy certificate generation script
COPY generate-certs.sh /generate-certs.sh
RUN chmod +x /generate-certs.sh

# Set entrypoint to generate certificates
ENTRYPOINT ["/generate-certs.sh"]