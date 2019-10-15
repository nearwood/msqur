#. script.config

eval "$(ssh-agent -s)"
chmod 600 ~/.ssh/id_rsa
ssh-add ~/.ssh/id_rsa

#TODO Run backup script on remote

rsync -av --delete --exclude='*/.git*:.svn*:*/config.php' -e "ssh -o StrictHostKeyChecking=no" src/ $DEPLOY_HOST/

rm ~/.ssh/id_rsa
#cp -v VERSION $DEPLOY_DIR/ > /dev/null 2>&1
