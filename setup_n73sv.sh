#!/bin/bash



echo '[archlinuxfr]\
SigLevel = Never\
Server = http://repo.archlinux.fr/$arch' >> /etc/pacman.conf



pacman -Syy ntp yaourt openssh jre7-openjdk bison rsync nginx apache redis bison php-apache \
  php-xsl apache php-apache phpmyadmin  php-mcrypt php-pgsql php-gd jre7-openjdk \
  samba jdk7-openjdk xbmc xorg-xinit pulseaudio lirc udisks upower xorg-xinit unrar \
  xorg-server xorg-apps xf86-input-evdev slim xf86-input-synaptics \
  nvidia \
  nodejs ntp  yaourt sysstat wget nginx fakeroot binutils linux-headers rsync python php mariadb postgresql \
  xorg-server xorg-apps slim  xf86-input-synaptics openbox xorg-xinit nvidia xterm \
  xorg-twm xorg-xclock xorg-twm xf86-video-intel openbox obconf libxslt terminator menumaker \
  gdisk xcompmgr tint2 bumblebee  bbswitch nvidia intel-dri xf86-video-intel slim-themes \
  archlinux-themes-slim ttf-bitstream-vera ttf-dejavu ttf-freefont ttf-linux-libertine \
  ttf-droid ttf-liberation ttf-ubuntu-font-family gnome-icon-theme git obconf transset-df \
  yajl virtualbox net-tools virtualbox-sdk vde2 qt4 \
  bumblebee  bbswitch nvidia intel-dri xf86-video-intel \
  slim-themes archlinux-themes-slim \
  ttd-bitstream-vera ttf-dejavu ttf-freefont ttf-linux-libertine ttf-droid ttf-liberation \
  ttf-ubuntu-font-family \
  chromium opera gstreamer0.10-good gstreamer0.10-base-plugins \
  alsa-utils alsa-plugins  alsa-oss



ntpd -q

systemctl start ntpd
systemctl enable ntpd



yaourt -Syua



systemctl start httpd
systemctl enable httpd
groupadd httpd
useradd -m -g httpd  -s /bin/bash httpd



