. script.config

theDate=`date "+%Y-%m-%d"`
fileName=$BACKUP_DIR/backup-$theDate.tar.bz2
dbFileName=$BACKUP_DIR/backup-$theDate.db.bz2

if [ -f $fileName ]
then
 echo "Backup file \"$fileName\" already exists. Skipping file backup.";
else
 echo "Backing up files..."
 tar -cjf $fileName $DEPLOY_DIR
 if [ $? -eq 0 ]
 then
  chmod 600 $fileName
  echo "File backup complete."
 fi
fi

if [ -f $dbFileName ]
then
 echo "DB backup file \"$dbFileName\" already exists. Skipping DB backup.";
else
 #TODO Straight to bzip isn't so great... need error check at each step
 echo "Backing up DB..."
 mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME | bzip2 --best -c > $dbFileName

 if [ $? -eq 0 ]
 then
  chmod 600 $dbFileName
  echo "DB backup complete."
 else
  >&2 echo "DB backup failed."
 fi
fi
