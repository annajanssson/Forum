const express = require("express");
const mysql = require("mysql");
const bcrypt = require("bcryptjs");
const jwt = require("jsonwebtoken");

const app = express();
const secret = "EnHemlighetSomIngenKanGissaXyz123%&/";

app.use(express.json());

const con = mysql.createConnection({
  host: "localhost",
  user: "root",
  password: "",
  database: "forum",
});

app.listen(3000, () => {
  console.log("Servern körs på port 3000");
});

// Registrering
app.post("/register", async (req, res) => {
  const { firstname, lastname, userId, passwd } = req.body;

  try {
    // Generera salt och hashat lösenord
    const salt = await bcrypt.genSalt(10);
    const hashedPassword = await bcrypt.hash(passwd, salt);

    const sql =
      "INSERT INTO users (firstname, lastname, userId, passwd) VALUES (?, ?, ?, ?)";

    con.query(
      sql,
      [firstname, lastname, userId, hashedPassword],
      (err, result) => {
        if (err) {
          console.error("Database error:", err.message);
          return res.status(500).send("Database error");
        }
        res.send({
          id: result.insertId,
          firstname,
          lastname,
          userId,
        });
      }
    );
  } catch (err) {
    console.error("Server error:", err.message);
    res.status(500).send("Server error");
  }
});

// Inloggning
app.post("/login", (req, res) => {
  const { userId, passwd } = req.body;
  console.log("Försöker logga in med:", userId);

  const sql = "SELECT * FROM users WHERE userId = ?";
  con.query(sql, [userId], async (err, results) => {
    if (err) {
      console.error("Database error:", err.message);
      return res.status(500).send("Database error");
    }

    if (results.length === 0) {
      console.log("Ingen användare hittades med userId:", userId);
      return res.status(401).send("Fel användarnamn eller lösenord");
    }

    const user = results[0];
    console.log("Användare hittad:", user);

    // Verifiera lösenordet med bcrypt
    const isMatch = await bcrypt.compare(passwd, user.passwd);

    if (!isMatch) {
      console.log("Lösenord matchar inte.");
      return res.status(401).send("Fel användarnamn eller lösenord");
    }

    console.log("Inloggning lyckades för:", user.userId);

    // Skapa JWT-token
    const token = jwt.sign({ id: user.id, userId: user.userId }, secret, {
      expiresIn: "1h",
    });

    res.send({ message: "Inloggning lyckades", token });
  });
});
