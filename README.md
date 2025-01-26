![IskoCards Logo](https://github.com/jennarms/IskoCards/blob/main/assets/LogoHeader.png)

## Group 3 Members

- **Jenna Nadine D. Ramos**
- **Anhelov B. Samia**
- **Tommy M. Piñon**
- **Lucky P. Silo**
- **Angela C. Malinay**

---

## DATABASE STRUCTURE:

The database for **IskoCards** is designed with three main tables:

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

## APP FEATURES

### **Landing Page**:

### **User Account**:
- **Login**
    - **Validation: Checks if account exists, incorrect password/username**
- **Create Account**
    - **Validation: No special characters in name, Unique username, match password**
    
- **Dashboard:**
    - **View Account**
    - **Edit Account**:
        - **Change Profile Picture**
            - **Validation: There must be an attatched pic**
        - **Edit Name**
            - **Validation: no special characters aside from letters, spaces, periods and dashes**
        - **Edit Username**
            - **Validation: Unique username**
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
- **UI Developed** (Still up for further development)

---

✨ Stay tuned for more updates! ✨
