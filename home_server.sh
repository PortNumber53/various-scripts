#!/bin/bash


cat << 'EOF' >> /etc/pacman.conf
[archlinuxfr]
SigLevel = Never
Server = http://repo.archlinux.fr/$arch
EOF

pacman --sync --refresh yaourt

yaourt -Syua


pacman -S ntp openssh jre7-openjdk bison rsync nginx apache redis php-xsl \
 php-apache phpmyadmin php-mcrypt php-pgsql php-gd sysstat wget \
 jre7-openjdk jdk7-openjdk samba \
 xbmc xorg-xinit pulseaudio lirc udisks upower xorg-xinit unrar \
 xorg-server xorg-apps xf86-input-evdev slim xf86-input-synaptics \
 nvidia nvidia-libgl \
 nodejs \
 python php mariadb postgresql


pacman -S apache-ant ttf-dejavu php-pear php-xsl

cd /root
echo "JENKINS"

useradd -m -g users -G storage,network,optical -s /bin/bash jenkins
wget http://mirrors.jenkins-ci.org/war/latest/jenkins.war

mv jenkins.war /home/jenkins/

chown jenkins:users /home/jenkins/jenkins.war

cat << 'EOF' > /home/jenkins/jenkins.helper
java -jar /home/jenkins/jenkins.war &
EOF

cat << 'EOF' > /lib/systemd/system/jenkins.service
[Unit]
Description=Jenkins Daemon
After=syslog.target

[Service]
Type=forking
ExecStart=/usr/bin/sh /home/jenkins/jenkins.helper
User=jenkins

[Install]
WantedBy=multi-user.target
EOF


systemctl daemon-reload


yaourt pwauth
usermod -a -G pwauth jenkins

touch /var/log/jenkins.log
chown jenkins:users /var/log/jenkins.log



systemctl enable jenkins.service





ln -s /usr/bin/pwauth /usr/local/bin/pwauth


pear channel-discover nikic.github.com/pear
pear install channel://nikic.github.com/pear/PHPParser-0.9.3
sudo pear install phpunit/PHP_Timer
sudo pear install ezc/ConsoleTools
sudo pear install theseer/DirectoryScanner
sudo pear install theseer/fDOMDocument
sudo pear install theseer/fXSL
