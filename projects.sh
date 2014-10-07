#!/bin/bash


function new_git() {
	PROJECT=$1
    CURRENT_DIR=`pwd`


    cd /tmp
    mkdir $PROJECT
    cd $PROJECT
    git init

cat << 'EOF' > .gitignore
build/*
!build/phpcs.xml
cache.properties
cgi-bin/*
upload/
.idea/*
# Compiled source #
###################
*.com
*.class
*.dll
*.exe
*.o
*.so
# Packages #
############
# it's better to unpack these files and commit the raw source
# git has its own built in compression methods
*.7z
*.dmg
*.gz
*.iso
*.jar
*.rar
*.tar
*.zip
# Logs and databases #
######################
*.log
*.sql
*.sqlite
# OS generated files #
######################
.DS_Store*
ehthumbs.db
Icon?
Thumbs.db
EOF

    git add .gitignore
    git commit -m "Added standard .gitignore file"

    cd ..
    git clone --bare $PROJECT $PROJECT.git
	touch $PROJECT.git/git-daemon-export-ok

	scp -r $PROJECT.git $SERVER:~/git/$PROJECT.git

	ssh $SERVER 'cd ~/git/'$PROJECT'.git && git --bare update-server-info && cd hooks && mv post-update.sample post-update && chmod a+x post-update'

    cd $CURRENT_DIR
    git clone $SERVER:~/git/$PROJECT.git
    cd $PROJECT

    echo "Get started working!"
    echo ""

}
