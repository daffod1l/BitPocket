CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,              
    last_name VARCHAR(255) NOT NULL,               
    email VARCHAR(255) NOT NULL UNIQUE,            
    password VARCHAR(255) NOT NULL,                
    role ENUM('Teacher', 'Student') NOT NULL,      
    school_name VARCHAR(255) NOT NULL,             
    security_question VARCHAR(255) NOT NULL,       
    security_answer VARCHAR(255) NOT NULL,         
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);