CREATE OR REPLACE VIEW NetWorthAnalytics AS(
    SELECT b.name AS branch_name, s.branch AS branch_id, s.branch_worth FROM (
        SELECT C.home_branch AS branch, SUM(a.balance) AS branch_worth FROM Account a
        INNER JOIN Customers C on C.id = a.holder
        GROUP BY C.home_branch
    ) s INNER JOIN Branch b ON b.id = s.branch
);

CREATE OR REPLACE VIEW AccountAnalytics AS (
    SELECT b.name AS branch_name, s.branch AS branch_id, s.number_of_accounts FROM (
        SELECT C.home_branch AS branch, COUNT(a.balance) AS number_of_accounts FROM Account a
        INNER JOIN Customers C on C.id = a.holder
        GROUP BY C.home_branch
    ) s INNER JOIN Branch b ON b.id = s.branch
);
