#!/bin/bash

# Generate PHP gRPC code from protobuf files
# This script should be run after installing protoc and grpc_php_plugin

PROTO_DIR="api/src/Grpc/Proto"
OUTPUT_DIR="api/src/Grpc/Generated"

# Create output directory if it doesn't exist
mkdir -p "$OUTPUT_DIR"

# Generate PHP gRPC code
protoc --php_out="$OUTPUT_DIR" \
       --grpc_out="$OUTPUT_DIR" \
       --plugin=protoc-gen-grpc=/usr/local/bin/grpc_php_plugin \
       --proto_path="$PROTO_DIR" \
       "$PROTO_DIR/food_delivery.proto"

echo "gRPC PHP code generated successfully in $OUTPUT_DIR"
