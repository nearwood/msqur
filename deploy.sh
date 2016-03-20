. script.config

rsync -av --delete --exclude='*/.hg*' src/ $DEPLOY_DIR/
cp -v VERSION $DEPLOY_DIR/ > /dev/null 2>&1
