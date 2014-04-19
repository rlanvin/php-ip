#!/bin/sh

# Combine all files into one library file easy to include

cat ./src/IP.php > ip.lib.php
tail -n +2 ./src/IPv4.php >> ip.lib.php
tail -n +2 ./src/IPv6.php >> ip.lib.php
tail -n +2 ./src/IPBlock.php >> ip.lib.php
tail -n +2 ./src/IPv4Block.php >> ip.lib.php
tail -n +2 ./src/IPv6Block.php >> ip.lib.php
tail -n +2 ./src/IPBlockIterator.php >> ip.lib.php
