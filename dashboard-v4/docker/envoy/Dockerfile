FROM docker.io/envoyproxy/envoy:v1.23-latest
LABEL maintainer="Jeremy Zheng"

COPY envoy.yaml /etc/envoy/envoy.yaml
RUN chmod go+r /etc/envoy/envoy.yaml
