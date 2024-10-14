# How to reset the database:

if your database is not working properly, you can reset the database by following the steps below:

```bash
# migrate refresh the database
 php artisan migrate:refresh

# enter the mysql shell
 mysqldump -u root -proot --no-create-info mydb > backup/skripsi.sql
```

# Existing User (for testing)

```bash
# Admin
email: dosen1@umn.ac.id 
password: password

# Dosen
email: dosen1@umn.ac.id
password: password

# Mahasiswa
email: mahasiswa1@student.umn.ac.id
password: password
```
