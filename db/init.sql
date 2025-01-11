
CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);



ALTER TABLE users
ADD COLUMN is_admin BOOLEAN DEFAULT FALSE;


-- Создание администратора
INSERT INTO users (username, password_hash, is_admin) VALUES 
('admin', '$2y$10$drWM0ULWnVzaaSMhi.L4wegwpgbcvEO4Xjx0BKMEeLbi27QjiQ4p2', TRUE);
-- Пароль для пользователя admin: "admin123"


-- Таблица анкет
CREATE TABLE surveys (
    id SERIAL PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Таблица компонентов анкет
CREATE TABLE survey_components (
    id SERIAL PRIMARY KEY,
    survey_id INT REFERENCES surveys(id) ON DELETE CASCADE,
    type VARCHAR(50) NOT NULL, -- Тип компонента (checkbox, radio, text, etc.)
    label VARCHAR(255) NOT NULL,
    options TEXT -- JSON-опции для выбора (например, значения для radio или checkbox)
);

-- Таблица ответов пользователей на анкеты
CREATE TABLE survey_responses (
    id SERIAL PRIMARY KEY,
    user_id INT REFERENCES users(id) ON DELETE CASCADE,
    survey_id INT REFERENCES surveys(id) ON DELETE CASCADE,
    responses JSON NOT NULL, -- Ответы хранятся в формате JSON
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Предустановленные анкеты
INSERT INTO surveys (title, description) VALUES
('Customer Feedback', 'We value your feedback! Please fill out this survey.'),
('Employee Satisfaction', 'Tell us about your work experience.'),
('Product Interest', 'Help us understand which products interest you the most.');

-- Компоненты для анкет
INSERT INTO survey_components (survey_id, type, label, options) VALUES
-- Компоненты для анкеты 1
(1, 'text', 'What is your name?', NULL),
(1, 'radio', 'How satisfied are you with our service?', '["Very Satisfied", "Satisfied", "Neutral", "Dissatisfied", "Very Dissatisfied"]'),
(1, 'checkbox', 'Which services have you used?', '["Delivery", "Online Support", "In-store Purchase", "Others"]'),
-- Компоненты для анкеты 2
(2, 'text', 'What is your department?', NULL),
(2, 'radio', 'Do you feel valued at work?', '["Yes", "No", "Not Sure"]'),
(2, 'checkbox', 'What benefits are most important to you?', '["Healthcare", "Retirement Plan", "Paid Time Off", "Flexible Hours"]'),
-- Компоненты для анкеты 3
(3, 'text', 'What is your age?', NULL),
(3, 'radio', 'How likely are you to buy our product?', '["Very Likely", "Somewhat Likely", "Neutral", "Unlikely", "Very Unlikely"]'),
(3, 'checkbox', 'Which features are most important to you?', '["Price", "Quality", "Brand", "Customer Support"]');

ALTER TABLE survey_responses ADD COLUMN completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;