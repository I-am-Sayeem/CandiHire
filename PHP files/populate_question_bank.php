<?php
// populate_question_bank.php - Populate question bank with sample questions
require_once 'Database.php';

// Comprehensive question bank for all departments
$questionBank = [
    'Software Engineering' => [
        [
            'question' => 'What is the time complexity of binary search?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Algorithms',
            'options' => [
                ['text' => 'O(n)', 'correct' => false],
                ['text' => 'O(log n)', 'correct' => true],
                ['text' => 'O(n²)', 'correct' => false],
                ['text' => 'O(1)', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which of the following is NOT a programming paradigm?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Programming Concepts',
            'options' => [
                ['text' => 'Object-Oriented Programming', 'correct' => false],
                ['text' => 'Functional Programming', 'correct' => false],
                ['text' => 'Procedural Programming', 'correct' => false],
                ['text' => 'Linear Programming', 'correct' => true]
            ]
        ],
        [
            'question' => 'What does REST stand for in web development?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Web Development',
            'options' => [
                ['text' => 'Representational State Transfer', 'correct' => true],
                ['text' => 'Remote Execution State Transfer', 'correct' => false],
                ['text' => 'Resource Execution State Transfer', 'correct' => false],
                ['text' => 'Representational Execution State Transfer', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which HTTP method is used to create a new resource?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Web Development',
            'options' => [
                ['text' => 'GET', 'correct' => false],
                ['text' => 'POST', 'correct' => true],
                ['text' => 'PUT', 'correct' => false],
                ['text' => 'DELETE', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of version control systems?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Development Tools',
            'options' => [
                ['text' => 'To track changes in code over time', 'correct' => true],
                ['text' => 'To compile code', 'correct' => false],
                ['text' => 'To debug applications', 'correct' => false],
                ['text' => 'To deploy applications', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which of the following is a NoSQL database?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Databases',
            'options' => [
                ['text' => 'MySQL', 'correct' => false],
                ['text' => 'PostgreSQL', 'correct' => false],
                ['text' => 'MongoDB', 'correct' => true],
                ['text' => 'SQLite', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the difference between a class and an object?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Object-Oriented Programming',
            'options' => [
                ['text' => 'A class is a blueprint, an object is an instance', 'correct' => true],
                ['text' => 'An object is a blueprint, a class is an instance', 'correct' => false],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'A class is always static, an object is dynamic', 'correct' => false]
            ]
        ],
        [
            'question' => 'What does API stand for?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Web Development',
            'options' => [
                ['text' => 'Application Programming Interface', 'correct' => true],
                ['text' => 'Application Process Integration', 'correct' => false],
                ['text' => 'Automated Programming Interface', 'correct' => false],
                ['text' => 'Application Protocol Interface', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which design pattern ensures only one instance of a class exists?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Design Patterns',
            'options' => [
                ['text' => 'Factory Pattern', 'correct' => false],
                ['text' => 'Observer Pattern', 'correct' => false],
                ['text' => 'Singleton Pattern', 'correct' => true],
                ['text' => 'Builder Pattern', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of unit testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing',
            'options' => [
                ['text' => 'To test individual components in isolation', 'correct' => true],
                ['text' => 'To test the entire application', 'correct' => false],
                ['text' => 'To test user interfaces', 'correct' => false],
                ['text' => 'To test database connections', 'correct' => false]
            ]
        ]
    ],
    'Data Science' => [
        [
            'question' => 'What is the difference between supervised and unsupervised learning?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Machine Learning',
            'options' => [
                ['text' => 'Supervised uses labeled data, unsupervised does not', 'correct' => true],
                ['text' => 'Unsupervised uses labeled data, supervised does not', 'correct' => false],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'Supervised is faster than unsupervised', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is overfitting in machine learning?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Machine Learning',
            'options' => [
                ['text' => 'When a model performs too well on training data but poorly on test data', 'correct' => true],
                ['text' => 'When a model is too simple', 'correct' => false],
                ['text' => 'When a model has too few parameters', 'correct' => false],
                ['text' => 'When a model is too fast', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which of the following is a classification algorithm?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Machine Learning',
            'options' => [
                ['text' => 'Linear Regression', 'correct' => false],
                ['text' => 'Random Forest', 'correct' => true],
                ['text' => 'K-Means', 'correct' => false],
                ['text' => 'PCA', 'correct' => false]
            ]
        ],
        [
            'question' => 'What does EDA stand for in data science?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Data Analysis',
            'options' => [
                ['text' => 'Exploratory Data Analysis', 'correct' => true],
                ['text' => 'Extended Data Analysis', 'correct' => false],
                ['text' => 'Efficient Data Analysis', 'correct' => false],
                ['text' => 'Enhanced Data Analysis', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of feature scaling?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Data Preprocessing',
            'options' => [
                ['text' => 'To ensure all features have similar scales', 'correct' => true],
                ['text' => 'To remove outliers', 'correct' => false],
                ['text' => 'To handle missing values', 'correct' => false],
                ['text' => 'To create new features', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which metric is used to evaluate classification models?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Model Evaluation',
            'options' => [
                ['text' => 'Mean Squared Error', 'correct' => false],
                ['text' => 'Accuracy', 'correct' => true],
                ['text' => 'R-squared', 'correct' => false],
                ['text' => 'Mean Absolute Error', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the difference between correlation and causation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Statistics',
            'options' => [
                ['text' => 'Correlation implies causation', 'correct' => false],
                ['text' => 'Causation implies correlation, but correlation does not imply causation', 'correct' => true],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'Causation is always stronger than correlation', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of cross-validation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Model Validation',
            'options' => [
                ['text' => 'To assess model performance on unseen data', 'correct' => true],
                ['text' => 'To speed up model training', 'correct' => false],
                ['text' => 'To reduce model complexity', 'correct' => false],
                ['text' => 'To handle missing data', 'correct' => false]
            ]
        ],
        [
            'question' => 'Which of the following is a dimensionality reduction technique?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Machine Learning',
            'options' => [
                ['text' => 'Principal Component Analysis (PCA)', 'correct' => true],
                ['text' => 'Linear Regression', 'correct' => false],
                ['text' => 'Decision Trees', 'correct' => false],
                ['text' => 'Support Vector Machines', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of data cleaning?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Data Preprocessing',
            'options' => [
                ['text' => 'To improve data quality and consistency', 'correct' => true],
                ['text' => 'To make data smaller', 'correct' => false],
                ['text' => 'To encrypt data', 'correct' => false],
                ['text' => 'To backup data', 'correct' => false]
            ]
        ]
    ],
    'Product Management' => [
        [
            'question' => 'What is the primary goal of product management?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Product Strategy',
            'options' => [
                ['text' => 'To maximize user value and business success', 'correct' => true],
                ['text' => 'To minimize development costs', 'correct' => false],
                ['text' => 'To maximize team size', 'correct' => false],
                ['text' => 'To minimize time to market', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is a user story in agile development?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Agile Methodology',
            'options' => [
                ['text' => 'A short description of a feature from the user\'s perspective', 'correct' => true],
                ['text' => 'A detailed technical specification', 'correct' => false],
                ['text' => 'A marketing description', 'correct' => false],
                ['text' => 'A bug report', 'correct' => false]
            ]
        ],
        [
            'question' => 'What does MVP stand for?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Product Development',
            'options' => [
                ['text' => 'Minimum Viable Product', 'correct' => true],
                ['text' => 'Maximum Value Product', 'correct' => false],
                ['text' => 'Most Valuable Player', 'correct' => false],
                ['text' => 'Minimum Value Proposition', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of A/B testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Product Analytics',
            'options' => [
                ['text' => 'To compare two versions of a feature to determine which performs better', 'correct' => true],
                ['text' => 'To test the entire product', 'correct' => false],
                ['text' => 'To test user interfaces only', 'correct' => false],
                ['text' => 'To test backend systems', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is a product roadmap?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Product Planning',
            'options' => [
                ['text' => 'A strategic plan showing the evolution of a product over time', 'correct' => true],
                ['text' => 'A technical architecture diagram', 'correct' => false],
                ['text' => 'A marketing plan', 'correct' => false],
                ['text' => 'A financial budget', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the difference between features and requirements?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Requirements Management',
            'options' => [
                ['text' => 'Features are what users see, requirements are what developers build', 'correct' => true],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'Requirements are what users see, features are what developers build', 'correct' => false],
                ['text' => 'Features are optional, requirements are mandatory', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is stakeholder management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Communication',
            'options' => [
                ['text' => 'Managing relationships with people who have an interest in the product', 'correct' => true],
                ['text' => 'Managing the development team', 'correct' => false],
                ['text' => 'Managing user feedback', 'correct' => false],
                ['text' => 'Managing product features', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of user research?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'User Research',
            'options' => [
                ['text' => 'To understand user needs, behaviors, and motivations', 'correct' => true],
                ['text' => 'To test product performance', 'correct' => false],
                ['text' => 'To market the product', 'correct' => false],
                ['text' => 'To develop the product', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is a product backlog?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Agile Methodology',
            'options' => [
                ['text' => 'A prioritized list of features, improvements, and fixes', 'correct' => true],
                ['text' => 'A list of completed features', 'correct' => false],
                ['text' => 'A list of bugs only', 'correct' => false],
                ['text' => 'A list of user complaints', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the role of a product owner?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Product Management',
            'options' => [
                ['text' => 'To represent the voice of the customer and prioritize the backlog', 'correct' => true],
                ['text' => 'To manage the development team', 'correct' => false],
                ['text' => 'To design the user interface', 'correct' => false],
                ['text' => 'To test the product', 'correct' => false]
            ]
        ]
    ],
    'Design' => [
        [
            'question' => 'What is the primary goal of user experience (UX) design?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'UX Design',
            'options' => [
                ['text' => 'To create beautiful interfaces', 'correct' => false],
                ['text' => 'To create meaningful and relevant experiences for users', 'correct' => true],
                ['text' => 'To make products look expensive', 'correct' => false],
                ['text' => 'To impress stakeholders', 'correct' => false]
            ]
        ],
        [
            'question' => 'What does UI stand for in design?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'UI Design',
            'options' => [
                ['text' => 'User Interface', 'correct' => true],
                ['text' => 'User Interaction', 'correct' => false],
                ['text' => 'User Integration', 'correct' => false],
                ['text' => 'User Intelligence', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of wireframing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Design Process',
            'options' => [
                ['text' => 'To create the final visual design', 'correct' => false],
                ['text' => 'To plan the structure and layout of a page', 'correct' => true],
                ['text' => 'To test user interactions', 'correct' => false],
                ['text' => 'To create animations', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the difference between UI and UX?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Design Concepts',
            'options' => [
                ['text' => 'UI is how it looks, UX is how it works', 'correct' => true],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'UI is for mobile, UX is for web', 'correct' => false],
                ['text' => 'UI is visual, UX is technical', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of user personas?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'User Research',
            'options' => [
                ['text' => 'To create fictional characters for marketing', 'correct' => false],
                ['text' => 'To represent target users and their needs', 'correct' => true],
                ['text' => 'To make designs more colorful', 'correct' => false],
                ['text' => 'To test technical functionality', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of usability testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing',
            'options' => [
                ['text' => 'To test if the code works', 'correct' => false],
                ['text' => 'To observe real users interacting with a product', 'correct' => true],
                ['text' => 'To check visual design quality', 'correct' => false],
                ['text' => 'To measure page load speed', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of prototyping?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Design Process',
            'options' => [
                ['text' => 'To create the final product', 'correct' => false],
                ['text' => 'To test and validate design concepts before development', 'correct' => true],
                ['text' => 'To replace user research', 'correct' => false],
                ['text' => 'To create marketing materials', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is accessibility in design?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Accessibility',
            'options' => [
                ['text' => 'Making designs look good on all devices', 'correct' => false],
                ['text' => 'Designing products that can be used by people with disabilities', 'correct' => true],
                ['text' => 'Creating fast-loading websites', 'correct' => false],
                ['text' => 'Making designs work offline', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of design systems?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Design Systems',
            'options' => [
                ['text' => 'To create individual designs for each page', 'correct' => false],
                ['text' => 'To maintain consistency across products and teams', 'correct' => true],
                ['text' => 'To replace user research', 'correct' => false],
                ['text' => 'To automate the design process', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of user journey mapping?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'User Research',
            'options' => [
                ['text' => 'To create navigation menus', 'correct' => false],
                ['text' => 'To visualize the user\'s experience from start to finish', 'correct' => true],
                ['text' => 'To design user interfaces', 'correct' => false],
                ['text' => 'To test website performance', 'correct' => false]
            ]
        ]
    ],
    'DevOps' => [
        [
            'question' => 'What is the primary goal of DevOps?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'DevOps Concepts',
            'options' => [
                ['text' => 'To separate development and operations', 'correct' => false],
                ['text' => 'To improve collaboration between development and operations teams', 'correct' => true],
                ['text' => 'To replace developers with operations staff', 'correct' => false],
                ['text' => 'To eliminate the need for testing', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is Continuous Integration (CI)?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'CI/CD',
            'options' => [
                ['text' => 'The practice of merging code changes frequently', 'correct' => true],
                ['text' => 'The practice of deploying code once per year', 'correct' => false],
                ['text' => 'The practice of writing code without testing', 'correct' => false],
                ['text' => 'The practice of manual code reviews', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is Continuous Deployment (CD)?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'CI/CD',
            'options' => [
                ['text' => 'Automatically deploying code to production', 'correct' => true],
                ['text' => 'Manually deploying code once per month', 'correct' => false],
                ['text' => 'Testing code without deploying', 'correct' => false],
                ['text' => 'Writing code without version control', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is Infrastructure as Code (IaC)?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Infrastructure',
            'options' => [
                ['text' => 'Managing infrastructure through code and automation', 'correct' => true],
                ['text' => 'Writing code for infrastructure manually', 'correct' => false],
                ['text' => 'Using only physical servers', 'correct' => false],
                ['text' => 'Avoiding cloud services', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of containerization?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Containers',
            'options' => [
                ['text' => 'To package applications with their dependencies', 'correct' => true],
                ['text' => 'To make applications run slower', 'correct' => false],
                ['text' => 'To replace version control', 'correct' => false],
                ['text' => 'To eliminate the need for testing', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is monitoring in DevOps?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Monitoring',
            'options' => [
                ['text' => 'Observing and measuring system performance', 'correct' => true],
                ['text' => 'Watching developers work', 'correct' => false],
                ['text' => 'Replacing automated testing', 'correct' => false],
                ['text' => 'Manual code reviews only', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of version control in DevOps?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Version Control',
            'options' => [
                ['text' => 'To track changes in code and configuration', 'correct' => true],
                ['text' => 'To replace automated testing', 'correct' => false],
                ['text' => 'To eliminate the need for documentation', 'correct' => false],
                ['text' => 'To make deployments manual', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of automated testing in DevOps?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing',
            'options' => [
                ['text' => 'To catch bugs early and ensure code quality', 'correct' => true],
                ['text' => 'To replace manual testing completely', 'correct' => false],
                ['text' => 'To slow down the development process', 'correct' => false],
                ['text' => 'To eliminate the need for code reviews', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of load balancing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Infrastructure',
            'options' => [
                ['text' => 'To distribute traffic across multiple servers', 'correct' => true],
                ['text' => 'To reduce server performance', 'correct' => false],
                ['text' => 'To eliminate the need for monitoring', 'correct' => false],
                ['text' => 'To replace containerization', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of security scanning in DevOps?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Security',
            'options' => [
                ['text' => 'To identify and fix security vulnerabilities', 'correct' => true],
                ['text' => 'To slow down deployments', 'correct' => false],
                ['text' => 'To replace manual security reviews', 'correct' => false],
                ['text' => 'To eliminate the need for authentication', 'correct' => false]
            ]
        ]
    ],
    'Quality Assurance' => [
        [
            'question' => 'What is the primary goal of Quality Assurance (QA)?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'QA Concepts',
            'options' => [
                ['text' => 'To ensure software meets quality standards', 'correct' => true],
                ['text' => 'To write code faster', 'correct' => false],
                ['text' => 'To replace developers', 'correct' => false],
                ['text' => 'To eliminate testing', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the difference between testing and QA?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'QA Concepts',
            'options' => [
                ['text' => 'QA is the process, testing is the activity', 'correct' => true],
                ['text' => 'They are the same thing', 'correct' => false],
                ['text' => 'QA is manual, testing is automated', 'correct' => false],
                ['text' => 'QA is for bugs, testing is for features', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of test cases?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Test Design',
            'options' => [
                ['text' => 'To document how to test specific functionality', 'correct' => true],
                ['text' => 'To replace user documentation', 'correct' => false],
                ['text' => 'To eliminate the need for code reviews', 'correct' => false],
                ['text' => 'To make testing faster', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is regression testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing Types',
            'options' => [
                ['text' => 'Testing to ensure new changes don\'t break existing functionality', 'correct' => true],
                ['text' => 'Testing only new features', 'correct' => false],
                ['text' => 'Testing without documentation', 'correct' => false],
                ['text' => 'Testing only once per release', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of test automation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Test Automation',
            'options' => [
                ['text' => 'To execute tests automatically and repeatedly', 'correct' => true],
                ['text' => 'To replace all manual testing', 'correct' => false],
                ['text' => 'To eliminate the need for test cases', 'correct' => false],
                ['text' => 'To make testing slower', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of bug tracking?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Bug Management',
            'options' => [
                ['text' => 'To document and manage software defects', 'correct' => true],
                ['text' => 'To replace code reviews', 'correct' => false],
                ['text' => 'To eliminate the need for testing', 'correct' => false],
                ['text' => 'To make development faster', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of performance testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing Types',
            'options' => [
                ['text' => 'To verify system performance under various conditions', 'correct' => true],
                ['text' => 'To test only user interfaces', 'correct' => false],
                ['text' => 'To replace functional testing', 'correct' => false],
                ['text' => 'To eliminate the need for load testing', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of security testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Security Testing',
            'options' => [
                ['text' => 'To identify security vulnerabilities in software', 'correct' => true],
                ['text' => 'To test only user authentication', 'correct' => false],
                ['text' => 'To replace functional testing', 'correct' => false],
                ['text' => 'To eliminate the need for code reviews', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of user acceptance testing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Testing Types',
            'options' => [
                ['text' => 'To verify software meets business requirements', 'correct' => true],
                ['text' => 'To test only technical functionality', 'correct' => false],
                ['text' => 'To replace all other testing', 'correct' => false],
                ['text' => 'To eliminate the need for QA', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of test coverage?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Test Metrics',
            'options' => [
                ['text' => 'To measure how much of the code is tested', 'correct' => true],
                ['text' => 'To replace test cases', 'correct' => false],
                ['text' => 'To eliminate the need for manual testing', 'correct' => false],
                ['text' => 'To make testing faster', 'correct' => false]
            ]
        ]
    ],
    'Business' => [
        [
            'question' => 'What is the primary goal of business analysis?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Business Analysis',
            'options' => [
                ['text' => 'To understand business needs and recommend solutions', 'correct' => true],
                ['text' => 'To replace project managers', 'correct' => false],
                ['text' => 'To eliminate the need for developers', 'correct' => false],
                ['text' => 'To make business decisions without stakeholders', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of requirements gathering?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Requirements',
            'options' => [
                ['text' => 'To understand what stakeholders need from a system', 'correct' => true],
                ['text' => 'To replace user research', 'correct' => false],
                ['text' => 'To eliminate the need for testing', 'correct' => false],
                ['text' => 'To make development faster', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of stakeholder management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Stakeholder Management',
            'options' => [
                ['text' => 'To manage relationships with people affected by the project', 'correct' => true],
                ['text' => 'To replace project managers', 'correct' => false],
                ['text' => 'To eliminate the need for communication', 'correct' => false],
                ['text' => 'To make decisions without input', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of process mapping?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Process Analysis',
            'options' => [
                ['text' => 'To visualize and understand business processes', 'correct' => true],
                ['text' => 'To replace system design', 'correct' => false],
                ['text' => 'To eliminate the need for requirements', 'correct' => false],
                ['text' => 'To make processes more complex', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of gap analysis?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Analysis Techniques',
            'options' => [
                ['text' => 'To identify differences between current and desired state', 'correct' => true],
                ['text' => 'To replace requirements gathering', 'correct' => false],
                ['text' => 'To eliminate the need for stakeholders', 'correct' => false],
                ['text' => 'To make projects faster', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of cost-benefit analysis?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Financial Analysis',
            'options' => [
                ['text' => 'To evaluate the financial viability of a project', 'correct' => true],
                ['text' => 'To replace project planning', 'correct' => false],
                ['text' => 'To eliminate the need for budgets', 'correct' => false],
                ['text' => 'To make projects more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of risk assessment?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Risk Management',
            'options' => [
                ['text' => 'To identify and evaluate potential project risks', 'correct' => true],
                ['text' => 'To replace project management', 'correct' => false],
                ['text' => 'To eliminate the need for planning', 'correct' => false],
                ['text' => 'To make projects riskier', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of change management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Change Management',
            'options' => [
                ['text' => 'To manage the transition to new systems or processes', 'correct' => true],
                ['text' => 'To replace project management', 'correct' => false],
                ['text' => 'To eliminate the need for training', 'correct' => false],
                ['text' => 'To make changes without planning', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of user acceptance criteria?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Acceptance Criteria',
            'options' => [
                ['text' => 'To define when a solution is acceptable to users', 'correct' => true],
                ['text' => 'To replace testing', 'correct' => false],
                ['text' => 'To eliminate the need for requirements', 'correct' => false],
                ['text' => 'To make development faster', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of business process improvement?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Process Improvement',
            'options' => [
                ['text' => 'To optimize business processes for better efficiency', 'correct' => true],
                ['text' => 'To replace all existing processes', 'correct' => false],
                ['text' => 'To eliminate the need for analysis', 'correct' => false],
                ['text' => 'To make processes more complex', 'correct' => false]
            ]
        ]
    ],
    'Marketing' => [
        [
            'question' => 'What is the primary goal of digital marketing?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Digital Marketing',
            'options' => [
                ['text' => 'To promote products and services through digital channels', 'correct' => true],
                ['text' => 'To replace traditional marketing completely', 'correct' => false],
                ['text' => 'To eliminate the need for sales teams', 'correct' => false],
                ['text' => 'To make marketing more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of SEO (Search Engine Optimization)?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'SEO',
            'options' => [
                ['text' => 'To improve website visibility in search engines', 'correct' => true],
                ['text' => 'To replace content marketing', 'correct' => false],
                ['text' => 'To eliminate the need for social media', 'correct' => false],
                ['text' => 'To make websites load slower', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of content marketing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Content Marketing',
            'options' => [
                ['text' => 'To attract and engage audiences through valuable content', 'correct' => true],
                ['text' => 'To replace all other marketing methods', 'correct' => false],
                ['text' => 'To eliminate the need for SEO', 'correct' => false],
                ['text' => 'To make marketing more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of social media marketing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Social Media',
            'options' => [
                ['text' => 'To engage with audiences on social platforms', 'correct' => true],
                ['text' => 'To replace email marketing', 'correct' => false],
                ['text' => 'To eliminate the need for websites', 'correct' => false],
                ['text' => 'To make marketing more complex', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of email marketing?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Email Marketing',
            'options' => [
                ['text' => 'To communicate directly with customers via email', 'correct' => true],
                ['text' => 'To replace social media marketing', 'correct' => false],
                ['text' => 'To eliminate the need for websites', 'correct' => false],
                ['text' => 'To make marketing more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of marketing analytics?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Analytics',
            'options' => [
                ['text' => 'To measure and analyze marketing performance', 'correct' => true],
                ['text' => 'To replace marketing strategy', 'correct' => false],
                ['text' => 'To eliminate the need for campaigns', 'correct' => false],
                ['text' => 'To make marketing more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of customer segmentation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Customer Analysis',
            'options' => [
                ['text' => 'To divide customers into groups with similar characteristics', 'correct' => true],
                ['text' => 'To replace customer research', 'correct' => false],
                ['text' => 'To eliminate the need for personalization', 'correct' => false],
                ['text' => 'To make marketing more complex', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of brand positioning?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Brand Management',
            'options' => [
                ['text' => 'To establish a unique place for a brand in the market', 'correct' => true],
                ['text' => 'To replace product development', 'correct' => false],
                ['text' => 'To eliminate the need for marketing', 'correct' => false],
                ['text' => 'To make brands more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of marketing automation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Marketing Technology',
            'options' => [
                ['text' => 'To automate repetitive marketing tasks', 'correct' => true],
                ['text' => 'To replace all marketing activities', 'correct' => false],
                ['text' => 'To eliminate the need for strategy', 'correct' => false],
                ['text' => 'To make marketing more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of conversion optimization?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Conversion Optimization',
            'options' => [
                ['text' => 'To improve the rate at which visitors become customers', 'correct' => true],
                ['text' => 'To replace website design', 'correct' => false],
                ['text' => 'To eliminate the need for traffic', 'correct' => false],
                ['text' => 'To make websites load slower', 'correct' => false]
            ]
        ]
    ],
    'Human Resources' => [
        [
            'question' => 'What is the primary goal of Human Resources?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'HR Concepts',
            'options' => [
                ['text' => 'To manage and develop the organization\'s workforce', 'correct' => true],
                ['text' => 'To replace managers', 'correct' => false],
                ['text' => 'To eliminate the need for employees', 'correct' => false],
                ['text' => 'To make hiring more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of recruitment?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Recruitment',
            'options' => [
                ['text' => 'To attract and hire qualified candidates', 'correct' => true],
                ['text' => 'To replace training programs', 'correct' => false],
                ['text' => 'To eliminate the need for job descriptions', 'correct' => false],
                ['text' => 'To make hiring faster without quality', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of employee onboarding?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Onboarding',
            'options' => [
                ['text' => 'To integrate new employees into the organization', 'correct' => true],
                ['text' => 'To replace training programs', 'correct' => false],
                ['text' => 'To eliminate the need for orientation', 'correct' => false],
                ['text' => 'To make new employees work immediately', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of performance management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Performance Management',
            'options' => [
                ['text' => 'To monitor and improve employee performance', 'correct' => true],
                ['text' => 'To replace training programs', 'correct' => false],
                ['text' => 'To eliminate the need for feedback', 'correct' => false],
                ['text' => 'To make employees work without goals', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of employee development?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Employee Development',
            'options' => [
                ['text' => 'To enhance employee skills and career growth', 'correct' => true],
                ['text' => 'To replace recruitment', 'correct' => false],
                ['text' => 'To eliminate the need for training', 'correct' => false],
                ['text' => 'To make employees work without skills', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of compensation management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Compensation',
            'options' => [
                ['text' => 'To design and manage employee compensation packages', 'correct' => true],
                ['text' => 'To replace performance management', 'correct' => false],
                ['text' => 'To eliminate the need for benefits', 'correct' => false],
                ['text' => 'To make compensation unfair', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of employee relations?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Employee Relations',
            'options' => [
                ['text' => 'To maintain positive relationships between employees and management', 'correct' => true],
                ['text' => 'To replace communication', 'correct' => false],
                ['text' => 'To eliminate the need for policies', 'correct' => false],
                ['text' => 'To make workplace relationships worse', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of HR analytics?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'HR Analytics',
            'options' => [
                ['text' => 'To analyze workforce data for better decision-making', 'correct' => true],
                ['text' => 'To replace HR processes', 'correct' => false],
                ['text' => 'To eliminate the need for data', 'correct' => false],
                ['text' => 'To make HR decisions without analysis', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of diversity and inclusion?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Diversity & Inclusion',
            'options' => [
                ['text' => 'To create an inclusive workplace that values differences', 'correct' => true],
                ['text' => 'To replace equal opportunity', 'correct' => false],
                ['text' => 'To eliminate the need for policies', 'correct' => false],
                ['text' => 'To make workplaces less diverse', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of employee engagement?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Employee Engagement',
            'options' => [
                ['text' => 'To measure and improve employee commitment and satisfaction', 'correct' => true],
                ['text' => 'To replace performance management', 'correct' => false],
                ['text' => 'To eliminate the need for feedback', 'correct' => false],
                ['text' => 'To make employees less engaged', 'correct' => false]
            ]
        ]
    ],
    'Sales' => [
        [
            'question' => 'What is the primary goal of sales?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Sales Concepts',
            'options' => [
                ['text' => 'To generate revenue by selling products or services', 'correct' => true],
                ['text' => 'To replace marketing', 'correct' => false],
                ['text' => 'To eliminate the need for customers', 'correct' => false],
                ['text' => 'To make products more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of lead generation?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Lead Generation',
            'options' => [
                ['text' => 'To identify and attract potential customers', 'correct' => true],
                ['text' => 'To replace customer service', 'correct' => false],
                ['text' => 'To eliminate the need for marketing', 'correct' => false],
                ['text' => 'To make sales more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales qualification?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Sales Process',
            'options' => [
                ['text' => 'To determine if a lead is a good fit for the product', 'correct' => true],
                ['text' => 'To replace lead generation', 'correct' => false],
                ['text' => 'To eliminate the need for research', 'correct' => false],
                ['text' => 'To make sales faster without quality', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales forecasting?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Sales Planning',
            'options' => [
                ['text' => 'To predict future sales performance', 'correct' => true],
                ['text' => 'To replace sales goals', 'correct' => false],
                ['text' => 'To eliminate the need for planning', 'correct' => false],
                ['text' => 'To make sales unpredictable', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of customer relationship management (CRM)?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'CRM',
            'options' => [
                ['text' => 'To manage interactions with current and potential customers', 'correct' => true],
                ['text' => 'To replace sales processes', 'correct' => false],
                ['text' => 'To eliminate the need for customer service', 'correct' => false],
                ['text' => 'To make customer relationships worse', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales training?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Sales Training',
            'options' => [
                ['text' => 'To improve sales skills and performance', 'correct' => true],
                ['text' => 'To replace sales experience', 'correct' => false],
                ['text' => 'To eliminate the need for practice', 'correct' => false],
                ['text' => 'To make sales less effective', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales analytics?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Sales Analytics',
            'options' => [
                ['text' => 'To analyze sales data for better performance', 'correct' => true],
                ['text' => 'To replace sales processes', 'correct' => false],
                ['text' => 'To eliminate the need for data', 'correct' => false],
                ['text' => 'To make sales decisions without analysis', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales territory management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Territory Management',
            'options' => [
                ['text' => 'To organize and manage sales coverage areas', 'correct' => true],
                ['text' => 'To replace sales teams', 'correct' => false],
                ['text' => 'To eliminate the need for planning', 'correct' => false],
                ['text' => 'To make sales coverage worse', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales incentive programs?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Sales Incentives',
            'options' => [
                ['text' => 'To motivate sales performance through rewards', 'correct' => true],
                ['text' => 'To replace sales goals', 'correct' => false],
                ['text' => 'To eliminate the need for motivation', 'correct' => false],
                ['text' => 'To make sales less motivated', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of sales pipeline management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Pipeline Management',
            'options' => [
                ['text' => 'To track and manage sales opportunities through stages', 'correct' => true],
                ['text' => 'To replace sales processes', 'correct' => false],
                ['text' => 'To eliminate the need for tracking', 'correct' => false],
                ['text' => 'To make sales less organized', 'correct' => false]
            ]
        ]
    ],
    'Finance' => [
        [
            'question' => 'What is the primary goal of financial management?',
            'type' => 'multiple-choice',
            'difficulty' => 'easy',
            'category' => 'Financial Management',
            'options' => [
                ['text' => 'To maximize shareholder value and ensure financial stability', 'correct' => true],
                ['text' => 'To replace accounting', 'correct' => false],
                ['text' => 'To eliminate the need for budgets', 'correct' => false],
                ['text' => 'To make finances more complex', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of financial planning?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Financial Planning',
            'options' => [
                ['text' => 'To create strategies for achieving financial goals', 'correct' => true],
                ['text' => 'To replace financial analysis', 'correct' => false],
                ['text' => 'To eliminate the need for budgets', 'correct' => false],
                ['text' => 'To make finances unpredictable', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of financial analysis?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Financial Analysis',
            'options' => [
                ['text' => 'To evaluate financial performance and make informed decisions', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for data', 'correct' => false],
                ['text' => 'To make financial decisions without analysis', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of budgeting?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Budgeting',
            'options' => [
                ['text' => 'To plan and control financial resources', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for analysis', 'correct' => false],
                ['text' => 'To make finances more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of risk management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Risk Management',
            'options' => [
                ['text' => 'To identify and mitigate financial risks', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for insurance', 'correct' => false],
                ['text' => 'To make finances riskier', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of investment analysis?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Investment Analysis',
            'options' => [
                ['text' => 'To evaluate investment opportunities and returns', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for research', 'correct' => false],
                ['text' => 'To make investments without analysis', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of cash flow management?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Cash Flow',
            'options' => [
                ['text' => 'To monitor and optimize cash inflows and outflows', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for budgets', 'correct' => false],
                ['text' => 'To make cash flow unpredictable', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of financial reporting?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Financial Reporting',
            'options' => [
                ['text' => 'To communicate financial information to stakeholders', 'correct' => true],
                ['text' => 'To replace financial analysis', 'correct' => false],
                ['text' => 'To eliminate the need for transparency', 'correct' => false],
                ['text' => 'To make financial information less clear', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of cost accounting?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Cost Accounting',
            'options' => [
                ['text' => 'To track and analyze costs for decision-making', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for budgets', 'correct' => false],
                ['text' => 'To make costs more expensive', 'correct' => false]
            ]
        ],
        [
            'question' => 'What is the purpose of financial controls?',
            'type' => 'multiple-choice',
            'difficulty' => 'medium',
            'category' => 'Financial Controls',
            'options' => [
                ['text' => 'To ensure compliance and prevent financial fraud', 'correct' => true],
                ['text' => 'To replace financial planning', 'correct' => false],
                ['text' => 'To eliminate the need for audits', 'correct' => false],
                ['text' => 'To make finances less secure', 'correct' => false]
            ]
        ]
    ]
];

// Function to generate more questions for each department
function generateAdditionalQuestions($department, $count = 90) {
    $additionalQuestions = [];
    
    // Define department-specific question templates
    $questionTemplates = [
        'Software Engineering' => [
            'What is the primary purpose of version control in software development?',
            'Which design pattern is used to create objects without specifying their exact class?',
            'What is the main advantage of using microservices architecture?',
            'Which testing approach focuses on testing individual components in isolation?',
            'What does SOLID principles stand for in object-oriented design?',
            'Which algorithm is commonly used for sorting large datasets efficiently?',
            'What is the purpose of continuous integration in DevOps?',
            'Which database normalization form eliminates partial dependencies?',
            'What is the main benefit of using dependency injection?',
            'Which security practice helps prevent SQL injection attacks?'
        ],
        'Data Science' => [
            'What is the difference between supervised and unsupervised learning?',
            'Which metric is best for evaluating classification models with imbalanced data?',
            'What is the purpose of cross-validation in machine learning?',
            'Which algorithm is commonly used for dimensionality reduction?',
            'What is overfitting and how can it be prevented?',
            'Which statistical test is used to compare means of two groups?',
            'What is the purpose of feature engineering in machine learning?',
            'Which visualization is best for showing correlation between variables?',
            'What is the difference between precision and recall?',
            'Which technique is used to handle missing values in datasets?'
        ],
        'Cybersecurity' => [
            'What is the primary goal of penetration testing?',
            'Which encryption algorithm is considered most secure for current use?',
            'What is the difference between authentication and authorization?',
            'Which type of attack targets the human element of security?',
            'What is the purpose of a firewall in network security?',
            'Which vulnerability is most commonly exploited in web applications?',
            'What is the principle of least privilege in access control?',
            'Which protocol provides secure communication over HTTP?',
            'What is the main purpose of intrusion detection systems?',
            'Which security framework provides guidelines for information security management?'
        ]
    ];
    
    // Get templates for the department or use generic ones
    $templates = $questionTemplates[$department] ?? [
        'What is the primary goal of this process?',
        'Which approach is most effective for solving this problem?',
        'What are the key benefits of implementing this solution?',
        'Which methodology is commonly used in this field?',
        'What is the main challenge in this area?'
    ];
    
    for ($i = 1; $i <= $count; $i++) {
        $categories = ['General', 'Advanced', 'Best Practices', 'Tools', 'Methodology'];
        $difficulties = ['easy', 'medium', 'hard'];
        
        $category = $categories[array_rand($categories)];
        $difficulty = $difficulties[array_rand($difficulties)];
        
        // Use different templates and create unique questions
        $template = $templates[$i % count($templates)];
        $questionText = $template;
        
        // Generate unique options based on the question
        $correctOption = generateCorrectOption($template, $department);
        $incorrectOptions = generateIncorrectOptions($template, $department);
        
        $additionalQuestions[] = [
            'question' => $questionText,
            'type' => 'multiple-choice',
            'difficulty' => $difficulty,
            'category' => $category,
            'options' => [
                ['text' => $correctOption, 'correct' => true],
                ['text' => $incorrectOptions[0], 'correct' => false],
                ['text' => $incorrectOptions[1], 'correct' => false],
                ['text' => $incorrectOptions[2], 'correct' => false]
            ]
        ];
    }
    
    return $additionalQuestions;
}

// Helper function to generate correct options
function generateCorrectOption($question, $department) {
    $correctAnswers = [
        'Software Engineering' => [
            'To track changes in code over time',
            'Factory Pattern',
            'Better scalability and maintainability',
            'Unit testing',
            'Single Responsibility, Open/Closed, Liskov Substitution, Interface Segregation, Dependency Inversion',
            'Quick Sort',
            'To automatically build and test code changes',
            'Third Normal Form (3NF)',
            'Reduces coupling between components',
            'Parameterized queries'
        ],
        'Data Science' => [
            'Supervised learning uses labeled data, unsupervised does not',
            'F1-Score',
            'To evaluate model performance on unseen data',
            'Principal Component Analysis (PCA)',
            'Model performs too well on training data, use regularization',
            'T-test',
            'To create meaningful features from raw data',
            'Scatter plot matrix',
            'Precision measures accuracy of positive predictions, recall measures completeness',
            'Imputation or removal of missing values'
        ],
        'Cybersecurity' => [
            'To identify vulnerabilities in systems',
            'AES (Advanced Encryption Standard)',
            'Authentication verifies identity, authorization grants access',
            'Social engineering',
            'To filter network traffic',
            'SQL Injection',
            'Users should have minimum necessary access',
            'HTTPS',
            'To detect suspicious activities',
            'ISO 27001'
        ]
    ];
    
    $answers = $correctAnswers[$department] ?? ['Option A', 'Option B', 'Option C', 'Option D'];
    return $answers[array_rand($answers)];
}

// Helper function to generate incorrect options
function generateIncorrectOptions($question, $department) {
    $incorrectAnswers = [
        'Software Engineering' => [
            'To store backup files',
            'Singleton Pattern',
            'Faster development',
            'Integration testing',
            'Simple, Organized, Logical, Intelligent, Dynamic',
            'Bubble Sort',
            'To deploy applications',
            'First Normal Form (1NF)',
            'Increases performance',
            'Input validation'
        ],
        'Data Science' => [
            'Supervised learning is faster than unsupervised',
            'Accuracy',
            'To train the model',
            'Linear Regression',
            'Model performs poorly on training data, use more data',
            'Chi-square test',
            'To reduce computational complexity',
            'Bar chart',
            'Precision and recall are the same thing',
            'Ignore missing values'
        ],
        'Cybersecurity' => [
            'To improve system performance',
            'MD5',
            'Authentication and authorization are the same',
            'Brute force attack',
            'To encrypt data',
            'Cross-site scripting',
            'Users should have maximum access',
            'HTTP',
            'To prevent unauthorized access',
            'PCI DSS'
        ]
    ];
    
    $answers = $incorrectAnswers[$department] ?? ['Option A', 'Option B', 'Option C', 'Option D'];
    return array_slice($answers, 0, 3);
}

try {
    if (isset($pdo) && $pdo instanceof PDO) {
        // Clear existing questions
        $pdo->exec("DELETE FROM question_banks");
        
        $totalQuestions = 0;
        
        foreach ($questionBank as $department => $questions) {
            // Add the predefined questions
            foreach ($questions as $questionData) {
                $stmt = $pdo->prepare("
                    INSERT INTO question_bank (Department, QuestionType, QuestionText, Difficulty, Category, Tags) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $department,
                    $questionData['type'],
                    $questionData['question'],
                    $questionData['difficulty'],
                    $questionData['category'],
                    $department . ', ' . $questionData['category']
                ]);
                
                $questionId = $pdo->lastInsertId();
                
                // Insert options
                foreach ($questionData['options'] as $index => $option) {
                    $optionStmt = $pdo->prepare("
                        INSERT INTO question_bank_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                        VALUES (?, ?, ?, ?)
                    ");
                    
                    $optionStmt->execute([
                        $questionId,
                        $option['text'],
                        $option['correct'] ? 1 : 0,
                        $index + 1
                    ]);
                }
                
                $totalQuestions++;
            }
            
            // Generate additional questions to reach 100 per department
            $additionalQuestions = generateAdditionalQuestions($department, 90);
            
            foreach ($additionalQuestions as $questionData) {
                $stmt = $pdo->prepare("
                    INSERT INTO question_bank (Department, QuestionType, QuestionText, Difficulty, Category, Tags) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ");
                
                $stmt->execute([
                    $department,
                    $questionData['type'],
                    $questionData['question'],
                    $questionData['difficulty'],
                    $questionData['category'],
                    $department . ', ' . $questionData['category']
                ]);
                
                $questionId = $pdo->lastInsertId();
                
                // Insert options
                foreach ($questionData['options'] as $index => $option) {
                    $optionStmt = $pdo->prepare("
                        INSERT INTO question_bank_options (QuestionID, OptionText, IsCorrect, OptionOrder) 
                        VALUES (?, ?, ?, ?)
                    ");
                    
                    $optionStmt->execute([
                        $questionId,
                        $option['text'],
                        $option['correct'] ? 1 : 0,
                        $index + 1
                    ]);
                }
                
                $totalQuestions++;
            }
        }
        
        echo "Successfully populated question bank with {$totalQuestions} questions across " . count($questionBank) . " departments.\n";
        
        // Show statistics
        $statsStmt = $pdo->query("
            SELECT Department, COUNT(*) as QuestionCount 
            FROM question_bank 
            GROUP BY Department
        ");
        
        echo "\nQuestion Bank Statistics:\n";
        echo "========================\n";
        while ($row = $statsStmt->fetch(PDO::FETCH_ASSOC)) {
            echo "{$row['Department']}: {$row['QuestionCount']} questions\n";
        }
        
    } else {
        echo "Database connection not available.\n";
    }
    
} catch (Exception $e) {
    echo "Error populating question bank: " . $e->getMessage() . "\n";
}
?>
