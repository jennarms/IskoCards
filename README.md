![IskoCards Logo](https://github.com/jennarms/IskoCards/blob/main/assets/LogoHeader.png)

## Group 3 Members

- **Jenna Nadine D. Ramos**
- **Anhelov B. Samia**
- **Tommy M. Piñon**
- **Lucky P. Silo**
- **Angela C. Malinay**

---

## DATABASE STRUCTURE:

#### **Create Database, Use Database**
- **You can set any name for database but in this code this is the one used.**
```sql
CREATE DATABASE iskocards_db;
USE iskocards_db;
```
- **The database for **IskoCards** is designed with three main tables: users, folders, flashcards**

#### **Users Table**
```sql
CREATE TABLE users (
    id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    profile_picture VARCHAR(255) DEFAULT 'default.jpg',
    storage_used DECIMAL(10,2) UNSIGNED DEFAULT 0 COMMENT 'Storage used in KB',
    storage_limit DECIMAL(10,2) UNSIGNED DEFAULT 5120 COMMENT 'Storage limit in KB (default: 5MB)',
    role ENUM('user', 'admin') DEFAULT 'user' NOT NULL COMMENT 'User role (user or admin)',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL
);
```

#### **Folders Table**
```sql
CREATE TABLE folders (
    folder_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT(11) UNSIGNED NOT NULL,
    folder_name VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
#### **Flashcards Table**
```sql
CREATE TABLE flashcards (
    flashcard_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    folder_id INT(11) UNSIGNED NOT NULL,
    user_id INT(11) UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    answer TEXT NOT NULL,
    size_kb DECIMAL(10,2) UNSIGNED DEFAULT 0 COMMENT 'Size of the flashcard in KB',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (folder_id) REFERENCES folders(folder_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);
```
---

## ADMIN MUST BE INSERTED MANUALLY:
-- **Run PHP Server "generate_hash.php"**
![Run PHP Server "generate_hash.php](https://raw.githubusercontent.com/jennarms/IskoCards/refs/heads/main/Github%20Demo%20Pics/Insert%20Admin%201.png)

-- **Paste the generated hashed password in the insert into command**
![Paste Hashed to comman](https://raw.githubusercontent.com/jennarms/IskoCards/refs/heads/main/Github%20Demo%20Pics/Insert%20Admin%202.png)

-- **Insert Into table**
```sql
INSERT INTO users (name, username, password, role) 
VALUES ('IskoCards Admin', 'iskocardsadmin', 'paste-generated-hashed-here', 'admin');
```

-- **Run SQL Command, check if admin is inserted**

---

## APP FEATURES

### **Landing Page**:

### **User Account**:
- **Sign in**
    - **Validation: No special characters in name, Unique username, match password**
- **Login**
    - **Validation: Checks if account exists, incorrect password/username**
    
- **Dashboard:**
    - **View Account**
    - **Edit Account**:
        - **Change Profile Picture**
            - **Validation: There must be an attatched pic**
        - **Edit Name**
            - **Validation: no special characters aside from letters, spaces, periods and dashes**
        - **Edit Username**
            - **Validation: Unique username**
        - **Edit Password**
            - **Validation: Match password**
    - **Logout**
    - **Privacy Policy Page**
    - **About Page**
    - **Storage Management Progress Bar**
        - **Validations:**
            - **In dashboard: A note that if 100% filled, will show "You have reached your storage limit!"**
            - **In dashboard: A note that if 80% filled, will show You are at 80% of your storage limit!**
            - **In flashcards management: page when there is no storage "You have reached your storage limit. Cannot add, edit, or delete flashcards." will be displayed**

- **Folder Management**
    - **Create Folder**
        - **Validation: name folder not empty, check duplicate name**
    - **Edit Folder**
        - **Validation: name folder not empty, check duplicate name**
    - **Delete Folder**
        - **Validation: confirmation before deletion**
    - **Search Bar: Search Folder**
    - **Link to Flashcards Webpage**
    - **Summary of Total Folders and Flashcards**

- **Flashcard Management**
    - **Create Flashcard**
        - **Validation: question and answer fields not empty, notif when not enough storage**
    - **Edit Flashcard**
        - **Validation: question and answer folder not empty**
    - **Delete Flashcard**
        - **Validation: confirmation before deletion**
    - **Search Bar: Search Flashcard Question**
    - **Summary Flashcards in each Folder**
    - **Review Flashcard**
        - **Validation: No Flashcards to be reviewed**
        - **Elapsed Time**
        - **Shuffle Option**
        - **Preview and Next**
        - **Keyboard Shortcuts**
        - **Info button**

### Admin Account:
- **Sidebar**
    - **Profile Info**
    - **Edit Account Feature**
        - **Change Profile Picture**
            - **Validation: There must be an attatched pic**
        - **Edit Name**
            - **Validation: no special characters aside from letters, spaces, periods and dashes**
        - **Edit Username**
            - **Validation: Unique username**
        - **Edit Password**
            - **Validation: Match password**
    - **Logout**
        - **Validation: Confirm Logout**
    - **Real Time Clock**

- **Dashboard**
    - **Dashboard Stats**
        - **Display number of total users and flashcard**
    - **User Management**
        - **Users search**
        - **View users list table**
        - **Delete user/s**
    - **Flashcard Management**
        - **Flashcards search**
        - **View flashcards list table**
        - **Delete flashcards/s**
---
