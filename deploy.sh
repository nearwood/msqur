#. script.config

eval "$(ssh-agent -s)"
chmod 600 .travis/deploy_key.pem
ssh-add .travis/deploy_key.pem

#TODO Run backup script on remote

rsync -av --delete --exclude='*/.git*:.svn*:*/config.php' src/ $DEPLOY_DIR/

rm .travis/deploy_key.pem
#cp -v VERSION $DEPLOY_DIR/ > /dev/null 2>&1
