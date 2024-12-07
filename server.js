const express = require('express');
const mysql = require('mysql2');
const cors = require('cors');

const app = express();
app.use(express.json());
app.use(cors());

const db = mysql.createConnection({
  host: 'localhost',
  user: 'root',
  password: '',
  database: 'ataxx_game',
});

db.connect((err) => {
  if (err) {
    console.error('Database connection error: ' + err.stack);
    return;
  }
  console.log('Connected to the database');
});

app.post('/api/player', (req, res) => {
  const { playerName } = req.body;
  if (!playerName) {
    return res.status(400).json({ message: 'Player name is required' });
  }

  const query = 'INSERT INTO players (name) VALUES (?)';
  db.query(query, [playerName], (err, results) => {
    if (err) {
      return res.status(500).json({ message: 'Error saving player data' });
    }
    res.status(201).json({ message: 'Player saved successfully', playerId: results.insertId });
  });
});

app.get('/api/player/:id', (req, res) => {
  const playerId = req.params.id;
  const query = 'SELECT * FROM players WHERE id = ?';
  db.query(query, [playerId], (err, results) => {
    if (err) {
      return res.status(500).json({ message: 'Error fetching player data' });
    }
    if (results.length === 0) {
      return res.status(404).json({ message: 'Player not found' });
    }
    res.status(200).json(results[0]);
  });
});

const port = 3000;
app.listen(port, () => {
  console.log(`Server is running on port ${port}`);
});
