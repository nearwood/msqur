#. script.config

mkdir -p ~\/.ssh
openssl aes-256-cbc -K $encrypted_571de2096706_key -iv $encrypted_571de2096706_iv -in .travis/travis_ci.enc -out .travis/id_rsa -d

eval "$(ssh-agent -s)"
chmod 600 .travis/id_rsa
ssh-add .travis/id_rsa

#TODO Run backup script on remote

rsync -av --delete --exclude-from 'deploy_excludes.txt' -e "ssh -o StrictHostKeyChecking=no" src/ $DEPLOY_HOST/

rm .travis/id_rsa
#cp -v VERSION $DEPLOY_DIR/ > /dev/null 2>&1
