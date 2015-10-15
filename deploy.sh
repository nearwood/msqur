. script.config

rsync -av --exclude='config.php' --exclude='*/.hg*' src/ $DEPLOY_DIR/
cp -v VERSION $DEPLOY_DIR/
