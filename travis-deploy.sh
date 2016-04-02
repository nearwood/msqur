
eval "$(ssh-agent -s)"
chmod 600 .travis/deploy_key.pem
ssh-add .travis/deploy_key.pem
git remote add deploy $DEPLOY_REPO
git push deploy
