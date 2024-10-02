#!/bin/bash

set -e

yarn add @grpc/grpc-js google-protobuf \
    marked dompurify jsdom canvas bufferutil utf-8-validate \
    pino pino-pretty

yarn add --dev webpack webpack-cli

echo 'done.'
exit 0
