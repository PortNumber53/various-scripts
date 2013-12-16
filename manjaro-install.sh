#!/bin/bash


sudo pacman -S firefox thunderbird chromium filezilla rsync wget git fakeroot jdk7-openjdk icedtea-web-java7

#Download java
#http://www.oracle.com/technetwork/java/javase/downloads/jdk7-downloads-1880260.html


echo "PHPStorm"
wget https://raw.github.com/ryad-eldajani/pkgbuild/master/phpstorm/PKGBUILD
makepkg

sudo pacman -U phpstorm-6.0.2-1-x86_64.pkg.tar.xz 
