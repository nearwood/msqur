. script.config

rsync -av --delete --exclude='*/.git*:.svn*' src/ $DEPLOY_DIR/
cp -v VERSION $DEPLOY_DIR/ > /dev/null 2>&1
