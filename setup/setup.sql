CREATE TYPE DirectionEnum AS ENUM('N', 'E', 'S', 'W');
CREATE TYPE BankRole AS ENUM('Teller', 'Loan Shark', 'Manager', 'Janitor');
CREATE TYPE AccountType AS ENUM('Checkings', 'Savings');
CREATE TYPE TransactionType AS ENUM('Deposit', 'Withdrawal', 'Transfer');

CREATE TABLE Addresses(
    id SERIAL PRIMARY KEY NOT NULL,
    number INT NOT NULL,
    direction DirectionEnum NOT NULL,
    street_name TEXT NOT NULL,
    city TEXT NOT NULL,
    state TEXT NOT NULL,
    zipcode CHAR(5) NOT NULL,
    unitNumber TEXT DEFAULT NULL
);


CREATE TABLE Branch(
    id SERIAL PRIMARY KEY NOT NULL,
    address INT REFERENCES Addresses(id) NOT NULL
);


CREATE TABLE Employee(
    id SERIAL PRIMARY KEY NOT NULL,
    name TEXT NOT NULL,
    role BankRole NOT NULL,
    address INT REFERENCES Addresses(id) NOT NULL,
    SSN CHAR(60) NOT NULL UNIQUE,  -- Saving Hashed Social Security Numbers
    branch INT REFERENCES Branch(id) NOT NULL,
    salary DOUBLE PRECISION NOT NULL
);


CREATE TABLE Customers(
    id SERIAL PRIMARY KEY NOT NULL,
    name TEXT NOT NULL,
    email TEXT NOT NULL, -- TODO: Add Regex check to make sure it's a valid email, maybe implement a bot to check
    phone INT NOT NULL,
    home_branch INT REFERENCES Branch(id) NOT NULL,
    address INT REFERENCES Addresses(id) NOT NULL
);


-- Accounts - checkings, savings,
CREATE TABLE Account(
    number SERIAL PRIMARY KEY NOT NULL,
    holder INT REFERENCES Customers(id) NOT NULL,
    type AccountType NOT NULL,
    balance DOUBLE PRECISION NOT NULL,
    account_name VARCHAR(15) DEFAULT NULL,
    interest FLOAT DEFAULT 0,
    monthly_fee FLOAT DEFAULT 0,
    can_go_negative BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (holder, type)
);


CREATE TABLE Logins(
    id SERIAL REFERENCES AccountHolders(id) PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL
);


CREATE TABLE AuthorizedUsers(
    account_number INT REFERENCES Account(number),
    owner_number INT REFERENCES accountholders(id)
);


CREATE TABLE Transactions(
    account_number INT REFERENCES account(number) NOT NULL,
    type TransactionType NOT NULL,
    amount DOUBLE PRECISION NOT NULL,
    description TEXT -- TODO: Figure out how to do this
);


CREATE TABLE LOANS(
    original_value DOUBLE PRECISION NOT NULL,
    apr DOUBLE PRECISION NOT NULL,
    n INT NOT NULL, -- Number of payments
    compounding_period INT NOT NULL -- Yearly, Monthly, as an integer
    -- TODO: Code an amortization table in PHP
);