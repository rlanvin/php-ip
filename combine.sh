#!/bin/sh

# Combine all files into one library file easy to include

cat ./src/IP.php
tail -n +2 ./src/IPv4.php
tail -n +2 ./src/IPv6.php
tail -n +2 ./src/IPBlock.php
tail -n +2 ./src/IPv4Block.php
tail -n +2 ./src/IPv6Block.php
tail -n +2 ./src/IPBlockIterator.php
