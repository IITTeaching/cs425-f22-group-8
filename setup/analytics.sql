CREATE OR REPLACE VIEW Analytics AS(
    SELECT b.name AS branch_name, s.branch AS branch_id, s.branch_worth FROM (
        SELECT C.home_branch AS branch, SUM(a.balance) AS branch_worth FROM Account a
        INNER JOIN Customers C on C.id = a.holder
        GROUP BY C.home_branch
    ) s INNER JOIN Branch b ON b.id = s.branch
);

-- TODO: Add number of accounts to Analytics
