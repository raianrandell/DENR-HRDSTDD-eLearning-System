-- =========================================
-- TABLE: User Instructors
-- =========================================
CREATE TABLE user_instructors (
  instructor_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  contact_number VARCHAR(12) NOT NULL,
  office VARCHAR(100),
  position VARCHAR(100),
  region VARCHAR(100),
  photo VARCHAR(255) DEFAULT NULL, -- Store image file path instead of BLOB
  `password` VARCHAR(255) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================================
-- TABLE: User Participants
-- =========================================
CREATE TABLE user_participants (
  participant_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  last_name VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  contact_number VARCHAR(12) NOT NULL,
  office VARCHAR(100),
  position VARCHAR(100),
  gender VARCHAR(10) NOT NULL,
  age INT(11) NOT NULL,
  salary_grade INT(11),
  photo VARCHAR(255) DEFAULT NULL, -- Store image file path instead of BLOB
  `password` VARCHAR(255) NOT NULL,
  in_training TINYINT(1) NOT NULL DEFAULT 0, -- 0 = Not in Training, 1 = In Training
  status VARCHAR(50) NOT NULL DEFAULT 'Active',
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================================
-- TABLE: Training / Courses
-- =========================================
CREATE TABLE training (
  training_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  training_title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NOT NULL,
  location VARCHAR(100),
  start_date DATE NOT NULL,
  end_date DATE NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- =========================================
-- TABLE: Training Participants (Junction)
-- =========================================
CREATE TABLE training_participants (
  training_id INT(11) NOT NULL,
  participant_id INT(11) NOT NULL,
  enrollment_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  completion_status ENUM('Enrolled', 'Completed', 'Dropped') DEFAULT 'Enrolled',
  PRIMARY KEY (training_id, participant_id),
  FOREIGN KEY (training_id) REFERENCES training(training_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (participant_id) REFERENCES user_participants(participant_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================================
-- TABLE: Training Instructors (Junction)
-- =========================================
CREATE TABLE training_instructors (
  training_id INT(11) NOT NULL,
  instructor_id INT(11) NOT NULL,
  assigned_date TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,

  PRIMARY KEY (training_id, instructor_id),
  FOREIGN KEY (training_id) REFERENCES training(training_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (instructor_id) REFERENCES user_instructors(instructor_id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- =========================================
-- TABLE: Grades
-- =========================================
CREATE TABLE grades (
  grade_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  training_id INT(11) DEFAULT NULL,
  participant_id INT(11) DEFAULT NULL,
  module_id INT(11) DEFAULT NULL,  -- Added module_id
  grade_given VARCHAR(255) DEFAULT NULL,
  graded_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  grade_status VARCHAR(50) NOT NULL DEFAULT 'Not Graded',
  feedback TEXT NULL DEFAULT 'No Feedback',

  FOREIGN KEY (training_id) REFERENCES training(training_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (participant_id) REFERENCES user_participants(participant_id) ON DELETE CASCADE ON UPDATE CASCADE,
  FOREIGN KEY (module_id) REFERENCES modules(module_id) ON DELETE CASCADE ON UPDATE CASCADE
);


-- =========================================
-- TABLE: Modules
-- =========================================
CREATE TABLE modules (
  module_id INT(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  module_name VARCHAR(255) NOT NULL,
  module_description TEXT DEFAULT NULL,
  module_type VARCHAR(50),
  training_id INT(11),
  file_path VARCHAR(255) DEFAULT NULL,
  link_url VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (training_id) REFERENCES training(training_id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE user_course_manager (
  id INT AUTO_INCREMENT PRIMARY KEY,
  first_name VARCHAR(100) NOT NULL,
  middle_name VARCHAR(100),
  last_name VARCHAR(100) NOT NULL,
  office VARCHAR(100),
  position VARCHAR(100),
  username VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  photo VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
