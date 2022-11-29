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
    name TEXT NOT NULL,
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


CREATE TABLE EmployeeLogins(
    id INT REFERENCES Employee(id) ON DELETE CASCADE PRIMARY KEY,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    totp_secret TEXT NOT NULL
);


CREATE TABLE Customers(
    id SERIAL PRIMARY KEY NOT NULL UNIQUE,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE CHECK ( email ~ '^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$' ),
    phone TEXT NOT NULL UNIQUE,
    home_branch INT REFERENCES Branch(id) NOT NULL,
    address INT REFERENCES Addresses(id) NOT NULL,
    authenticated_email BOOLEAN DEFAULT FALSE
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
    can_go_negative BOOLEAN DEFAULT FALSE
);


CREATE TABLE Logins(
    id INT REFERENCES Customers(id) ON DELETE CASCADE PRIMARY KEY ,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    totp_secret TEXT DEFAULT NULL
);

select * FROM Logins;

CREATE TABLE AuthorizedUsers(
    account_number INT REFERENCES Account(number) ON DELETE CASCADE,
    owner_number INT REFERENCES Customers(id)
);


CREATE TABLE Transactions(
    account_number INT REFERENCES account(number) NOT NULL,
    date DATE NOT NULL,
    type TransactionType NOT NULL,
    amount DOUBLE PRECISION NOT NULL,
    description TEXT, -- TODO: Figure out how to do this
    PRIMARY KEY (account_number, date, type)
);

CREATE TABLE AwaitingVerification(
    email TEXT NOT NULL REFERENCES Customers(email) PRIMARY KEY,
    name TEXT NOT NULL,
    time_of_creation INT NOT NULL
);

CREATE TABLE LoanRequests(
    customer_id INT NOT NULL REFERENCES Customers(id),
    amount FLOAT NOT NULL CHECK (amount > 0), -- Present Value (P)
    payback_period INT NOT NULL CHECK (payback_period > 0),  --
    compounding_per_year INT NOT NULL CHECK (compounding_per_year >= 1),
    apr FLOAT NOT NULL CHECK ( apr > 0 ),
    request_date DATE NOT NULL DEFAULT now()
); -- I = APR / Compounding per Year

CREATE TABLE LoanApprovals(
    loan_number SERIAL PRIMARY KEY NOT NULL,
    loan_name TEXT DEFAULT NULL,
    approver_id INT NOT NULL REFERENCES Employee(id),
    approval_date DATE NOT NULL DEFAULT now(),
    customer_id INT NOT NULL REFERENCES Customers(id),
    initial_amount FLOAT NOT NULL CHECK (initial_amount > 0),
    amount_remaining FLOAT NOT NULL CHECK (amount_remaining > 0),
    payback_period INT NOT NULL CHECK (payback_period > 0),
    compounding_per_year INT NOT NULL CHECK (compounding_per_year >= 1),
    apr FLOAT NOT NULL CHECK ( apr > 0 )
);

CREATE TABLE States(
    name TEXT NOT NULL UNIQUE,
    abbreviation CHAR(2) NOT NULL UNIQUE PRIMARY KEY
);

INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(2417, 'N', 'Western', 'Chicago', 'IL', '60629');
INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(6140, 'S', 'Wolcott', 'Chicago', 'IL', '60636');
INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(8456, 'E', 'Cottage Grove', 'Chicago', 'IL', '60654');
INSERT INTO Addresses(number, direction, street_name, city, state, zipcode) VALUES(4638, 'S', 'Woodlawn', 'Chicago', 'IL', '60653');

INSERT INTO Branch(name, address) VALUES('WCS Western', 2);
INSERT INTO Branch(name, address) VALUES('WCS Green Line', 3);
INSERT INTO Branch(name, address) VALUES('WCS Cottage Grove', 4);
INSERT INTO Branch(name, address) VALUES('WCS Woodlawn', 5);

INSERT INTO States VALUES('Alabama','AL');
INSERT INTO States VALUES('Alaska','AK');
INSERT INTO States VALUES('Arizona','AZ');
INSERT INTO States VALUES('Arkansas','AR');
INSERT INTO States VALUES('California','CA');
INSERT INTO States VALUES('Colorado','CO');
INSERT INTO States VALUES('Connecticut','CT');
INSERT INTO States VALUES('Delaware','DE');
INSERT INTO States VALUES('District of Columbia','DC');
INSERT INTO States VALUES('Florida','FL');
INSERT INTO States VALUES('Georgia','GA');
INSERT INTO States VALUES('Hawaii','HI');
INSERT INTO States VALUES('Idaho','ID');
INSERT INTO States VALUES('Illinois','IL');
INSERT INTO States VALUES('Indiana','IN');
INSERT INTO States VALUES('Iowa','IA');
INSERT INTO States VALUES('Kansas','KS');
INSERT INTO States VALUES('Kentucky','KY');
INSERT INTO States VALUES('Louisiana','LA');
INSERT INTO States VALUES('Maine','ME');
INSERT INTO States VALUES('Montana','MT');
INSERT INTO States VALUES('Nebraska','NE');
INSERT INTO States VALUES('Nevada','NV');
INSERT INTO States VALUES('New Hampshire','NH');
INSERT INTO States VALUES('New Jersey','NJ');
INSERT INTO States VALUES('New Mexico','NM');
INSERT INTO States VALUES('New York','NY');
INSERT INTO States VALUES('North Carolina','NC');
INSERT INTO States VALUES('North Dakota','ND');
INSERT INTO States VALUES('Ohio','OH');
INSERT INTO States VALUES('Oklahoma','OK');
INSERT INTO States VALUES('Oregon','OR');
INSERT INTO States VALUES('Maryland','MD');
INSERT INTO States VALUES('Massachusetts','MA');
INSERT INTO States VALUES('Michigan','MI');
INSERT INTO States VALUES('Minnesota','MN');
INSERT INTO States VALUES('Mississippi','MS');
INSERT INTO States VALUES('Missouri','MO');
INSERT INTO States VALUES('Pennsylvania','PA');
INSERT INTO States VALUES('Rhode Island','RI');
INSERT INTO States VALUES('South Carolina','SC');
INSERT INTO States VALUES('South Dakota','SD');
INSERT INTO States VALUES('Tennessee','TN');
INSERT INTO States VALUES('Texas','TX');
INSERT INTO States VALUES('Utah','UT');
INSERT INTO States VALUES('Vermont','VT');
INSERT INTO States VALUES('Virginia','VA');
INSERT INTO States VALUES('Washington','WA');
INSERT INTO States VALUES('West Virginia','WV');
INSERT INTO States VALUES('Wisconsin','WI');
INSERT INTO States VALUES('Wyoming','WY');