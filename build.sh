#!/bin/sh
MXMLC=../bin/mxmlc
OPTS=-use-network=false

$MXMLC $OPTS Graphs.mxml

mxmlFiles=`find */* -name '*.mxml' -print`

for mxml in ${mxmlFiles}; do
        echo "building $mxml"
        ($MXMLC $OPTS ${mxml})
done
