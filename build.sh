#!/bin/sh
MXMLC=../bin/mxmlc
OPTS=-use-network=false

$MXMLC $OPTS hello_flex.mxml

#mxmlFiles=`find */* -name '*.mxml' -print`

#for mxml in ${mxmlFiles}; do
        #echo "building $mxml"
        #($MXMLC $OPTS ${mxml})
#done
cp hello_flex.swf ir-search.php /Applications/MAMP/htdocs/ir/search
