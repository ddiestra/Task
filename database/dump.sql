CREATE TABLE records (
  id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  date_start DATE NOT NULL,
  date_end DATE NOT NULL,
  price FLOAT(7,2) NOT NULL
);

CREATE INDEX idx_date_start ON records(date_start);
CREATE INDEX idx_date_end ON records(date_end);