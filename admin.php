<?php
// Ashley Rabino - PHP Script for ASC Drop-in Tutoring Admin Interface
// Checks if the user is logged in before opening admin page
session_start();

if (!isset($_SESSION['admin_id'])) {
  header("Location: login.php");
  exit();
}

require_once 'db_config.php';

if (mysqli_connect_errno()) {
  exit("Error - could not connect to MySQL: " . mysqli_connect_error());
}

?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <title>ASC Tutor Management</title>
  <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

  <style>
    @import url("https://fonts.googleapis.com/css2?family=Bungee+Spice&family=DM+Sans:ital,opsz,wght@0,9..40,100..1000;1,9..40,100..1000&family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&family=Playfair+Display:ital,wght@0,600;1,600&family=Quicksand:wght@300..700&display=swap");

    * {
      font-family: "Inter", sans-serif;
      font-style: normal;
    }

    body {
      background-color: rgb(182, 182, 182);
      margin: 0;
    }

    .header-banner {
      width: 100%;
      height: 260px;
      overflow: hidden;
    }

    .header-banner img {
      object-fit: cover;
      width: 100%;
      height: 100%;
      object-position: center;
      display: block;
    }

    .arrow-feedback-container {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 15px;
      gap: 15px;
    }

    .search-bar {
      padding: 8px 10px;
      width: 165px;
      border: 1.5px solid black;
      border-radius: 5px;
    }

    .date-button {
      display: flex;
      align-items: center;
      gap: 5px;
      margin: 0 auto;
    }

    .arrow-button {
      cursor: pointer;
    }

    /* Container for the right-side buttons */
    .right-buttons {
      display: flex;
      gap: 10px;
    }

    .action-button {
      color: black;
      background-color: white;
      border: black solid 1.5px;
      border-radius: 5px;
      padding: 8px 10px;
      cursor: pointer;
    }

    .box {
      background-color: white;
      text-align: center;
      width: 83%;
      margin: 40px auto;
      margin-top: 20px;
      border-radius: 10px;
      padding: 35px;
      box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 15px -3px, rgba(0, 0, 0, 0.05) 0px 4px 6px -2px;
    }

    .section {
      margin-bottom: 25px;
      border-radius: 10px;
      border: 1.6px solid black;
      overflow: hidden;
    }

    .section-title {
      padding: 7px;
      text-align: left;
      font-weight: bold;
    }

    .late {
      background-color: #ffd580;
    }

    .active {
      background-color: #90ee90;
    }

    .upcoming {
      background-color: #9f9797;
    }

    .cancelled {
      background-color: #da7877;
    }

    .completed {
      background-color: violet;
    }

    h1 {
      text-align: left;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      background-color: white;
    }

    th {
      width: 20%;
      padding: 5px;
      text-align: center;
    }

    td {
      width: 20%;
      padding: 4px;
      text-align: center;
    }

    .status-full {
      background-color: #add8e6;
      padding: 4px;
    }

    .status-open {
      background-color: #cbc3e3;
      padding: 4px;
    }

    /* Cancellation confirmation box css */
    .cancel-overlay {
      position: fixed;
      inset: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }

    .cancel-box {
      background: white;
      border-radius: 10px;
      padding: 28px 32px;
      width: 450px;
      max-width: 90%;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
    }

    .cancel-box h2 {
      margin: 0 0 8px;
      font-size: 1.1rem;
    }

    .cancel-box p {
      font-size: 0.9rem;
    }

    .mode-buttons {
      display: flex;
      justify-content: center;
      gap: 30px;
      margin-top: 24px;
      margin-bottom: 30px;
    }

    .mode-option.selected {
      background: darkgray;
    }

    .confirm-buttons {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      margin-top: 18px;
    }
  </style>
</head>

<body>
  <div class="header-banner">
    <img src="data/UMBC header.png" alt="UMBC Academic Success Center" />
  </div>

  <div class="box">
    <h1>Drop-In Tutor Check-In System</h1>
    <div id="root"></div>
  </div>

  <script type="text/babel">
    const { useState, useEffect } = React;

    const SECTIONS = ["late", "active", "upcoming", "cancelled", "completed"];

    const sectionLabel = {
      late: "Late Shifts",
      active: "Active Shifts",
      upcoming: "Upcoming Shifts",
      cancelled: "Cancelled Shifts",
      completed: "Completed Shifts",
    };

    const STATUS_OPTIONS = {
      late: ["Late", "Active", "Cancelled"],
      active: ["Active", "Cancelled", "Completed"],
      upcoming: ["Upcoming", "Active", "Late", "Cancelled"],
      cancelled: ["Cancelled", "Active"],
      completed: [],
    };

    const ACTIVE_AVAILABILITY = ["Open", "Full"];

    const statusToSection = {
      Late: "late",
      Active: "active",
      Upcoming: "upcoming",
      Cancelled: "cancelled",
      Completed: "completed",
    };

    const sectionToStatus = {
      late: "Late",
      active: "Active",
      upcoming: "Upcoming",
      cancelled: "Cancelled",
      completed: "Completed",
    };

    // Confirmation box that appears when cancelling a shift
    function CancelMode({ tutor, onConfirm, onClose }) {
      const [mode, setMode] = useState(null);
      const [startDate, setStartDate] = useState('');
      const [endDate, setEndDate] = useState('');

      const handleConfirm = () => {
        if (!mode) return;

        // generates the array of dates for multiselect
        if (mode === 'multiday') {
          if (!startDate || !endDate) {
            alert("Please select both a start and end date.");
            return;
          }
          if (startDate > endDate) {
            alert("Start date must be before end date.");
            return;
          }

          let dates = [];
          let curr = new Date(startDate);
          let end = new Date(endDate);

          // Adjust for timezone offsets so dates don't accidentally shift backwards
          curr.setMinutes(curr.getMinutes() + curr.getTimezoneOffset());
          end.setMinutes(end.getMinutes() + end.getTimezoneOffset());

          while (curr <= end) {
            dates.push(curr.toISOString().split('T')[0]);
            curr.setDate(curr.getDate() + 1);
          }
          onConfirm(mode, dates);
        } else {
          onConfirm(mode, []); // single or today modes don't need the date array
        }
      };

      return (
        <div className="cancel-overlay" onClick={onClose}>
          <div className="cancel-box" onClick={(e) => e.stopPropagation()}>
            <h2>Cancel shift for {tutor.name}?</h2>
            <p>Please confirm how many shifts belonging to this tutor to cancel</p>

            <div className="mode-buttons">
              <button
                className={`mode-option ${mode === 'single' ? 'selected' : ''}`}
                onClick={() => setMode('single')}
              >
                This shift only
              </button>

              <button
                className={`mode-option ${mode === 'today' ? 'selected' : ''}`}
                onClick={() => setMode('today')}
              >
                All shifts today
              </button>

              <button
                className={`mode-option ${mode === 'multiday' ? 'selected' : ''}`}
                onClick={() => setMode('multiday')}
              >
                Multiple days
              </button>
            </div>

            {/* NEW: The Date Range Pickers! */}
            {mode === 'multiday' && (
              <div style={{ textAlign: 'center', marginBottom: '20px' }}>
                <label><b>Start Date:</b> <input type="date" value={startDate} onChange={(e) => setStartDate(e.target.value)} style={{ marginLeft: '5px', padding: '3px' }} /></label>
                <br /><br />
                <label><b>End Date:</b> <input type="date" value={endDate} onChange={(e) => setEndDate(e.target.value)} style={{ marginLeft: '11px', padding: '3px' }} /></label>
              </div>
            )}

            <div className="confirm-buttons">
              <button onClick={onClose} style={{ padding: '5px 10px', cursor: 'pointer' }}>Go back</button>
              <button
                onClick={handleConfirm}
                disabled={!mode}
                style={{ padding: '5px 10px', cursor: 'pointer', backgroundColor: mode ? '#da7877' : '#ccc', color: mode ? 'white' : 'black', border: 'none', borderRadius: '3px' }}
              >
                Confirm cancellation
              </button>
            </div>
          </div>
        </div>
      );
    }

    function App() {
      const [tutors, setTutors] = useState([]);
      const [currentDate, setCurrentDate] = useState(new Date());
      const [cancelMode, setCancelMode] = useState(null);
      // Search State from HTML file
      const [search, setSearch] = useState("");

      const getFormattedDate = (date) => {
        const offset = date.getTimezoneOffset()
        const dateLocal = new Date(date.getTime() - (offset * 60 * 1000))
        return dateLocal.toISOString().split('T')[0];
      };

      const loadSchedule = () => {
        const formattedDate = getFormattedDate(currentDate);

        fetch('get_sched.php?date=' + formattedDate)
          .then(async (response) => {
            const rawText = await response.text();
            try {
              const data = JSON.parse(rawText);
              if (data.success) {
                setTutors(data.tutors);
              } else {
                console.error("Failed to load:", data.message);
                setTutors([]);
              }
            } catch (e) {
              alert("React couldn't read the database output! The server said:\n\n" + rawText);
              setTutors([]);
            }
          })
          .catch(error => {
            alert("Network Fetch Error: " + error);
          });
      };

      useEffect(() => {
        loadSchedule();
      }, [currentDate]);

      const sendStatusUpdate = (shiftId, newStatus, targetDate = null) => {
        const updateDate = targetDate || getFormattedDate(currentDate);

        if (updateDate === getFormattedDate(currentDate)) {
          setTutors((prev) =>
            prev.map((t) =>
              t.id === shiftId ? { ...t, section: statusToSection[newStatus] } : t,
            ),
          );
        }

        fetch('update_status.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            shift_id: shiftId,
            new_status: newStatus,
            date: updateDate
          })
        })
          .then(response => response.json())
          .then(data => {
            // throw an alert if it failed on the current day
            if (!data.success && updateDate === getFormattedDate(currentDate)) {
              alert("Database Error: " + data.message);
              loadSchedule();
            }
          });
      };

      const changeSection = (id, newStatus) => {
        if (newStatus === 'Cancelled') {
          const tutor = tutors.find((t) => t.id === id);
          setCancelMode({ tutor });
        } else {
          sendStatusUpdate(id, newStatus);
        }
      };

      const handleCancelConfirm = (mode, selectedDates) => {
        const { tutor } = cancelMode;
        setCancelMode(null);

        if (mode === 'single') {
          sendStatusUpdate(tutor.id, 'Cancelled');
        }
        else if (mode === 'today') {
          const tutorsDayShifts = tutors.filter((t) => t.name === tutor.name);
          tutorsDayShifts.forEach((t) => sendStatusUpdate(t.id, 'Cancelled'));
        }
        else if (mode === 'multiday') {
          selectedDates.forEach(dateStr => {
            sendStatusUpdate(tutor.id, 'Cancelled', dateStr);
          });

          alert(`Cancellation requests sent! Note: Cancellations will only apply to future dates that have already been generated in the database.`);
        }
      };

      const changeAvailability = (id, val) => {
        setTutors((prev) =>
          prev.map((t) => (t.id === id ? { ...t, availability: val } : t)),
        );
      };

      const changeDate = (days) => {
        const newDate = new Date(currentDate);
        newDate.setDate(newDate.getDate() + days);
        setCurrentDate(newDate);
      };

      return (
        <div>
          {cancelMode && (
            <CancelMode
              tutor={cancelMode.tutor}
              onConfirm={handleCancelConfirm}
              onClose={() => setCancelMode(null)}
            />
          )}

          <div className="arrow-feedback-container">
            {/* SEARCH BAR IMPLEMENTATION */}
            <input
              type="text"
              placeholder="Search course or tutor"
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="search-bar"
            />

            <div className="date-button">
              <button className="arrow-button" onClick={() => changeDate(-1)}>
                &larr;
              </button>

              <input
                type="date"
                value={getFormattedDate(currentDate)}
                onChange={(e) => {
                  const selected = new Date(e.target.value);
                  selected.setMinutes(selected.getMinutes() + selected.getTimezoneOffset());
                  setCurrentDate(selected);
                }}
              />

              <button className="arrow-button" onClick={() => changeDate(1)}>
                &rarr;
              </button>
            </div>

            <div className="right-buttons">
              <button className="action-button" onClick={() => window.open('https://docs.google.com/spreadsheets/d/1zE-2hPtF0hfRsJfjtSxjRZRZ1DW-OaPZ6_9hUCb7ZTQ/edit?usp=sharing', '_blank')}>
                Access Feedback
              </button>
              <button className="action-button" onClick={() => window.location.href = 'logout.php'}>
                Logout
              </button>
            </div>
          </div>

          {SECTIONS.map((sec) => {
            // Filters the schedule both by section category and the search bar text
            const rows = tutors.filter((t) => {
              if (t.section !== sec) return false;
              if (search.trim() === "") return true;

              const searchTerm = search.toLowerCase();
              return (
                t.name.toLowerCase().includes(searchTerm) ||
                (t.course && t.course.toLowerCase().includes(searchTerm))
              );
            });

            const opts = STATUS_OPTIONS[sec];
            const isActive = sec === "active";
            const isCompleted = sec === "completed";
            const colCount = isActive ? 6 : isCompleted ? 4 : 5;

            return (
              <div className="section" key={sec}>
                <div className={`section-title ${sec}`}>
                  {sectionLabel[sec]}
                </div>

                <table>
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Course(s)</th>
                      {!isCompleted && <th>Status</th>}
                      {isActive && <th>Availability</th>}
                    </tr>
                  </thead>
                  <tbody>
                    {rows.length === 0 && (
                      <tr>
                        <td colSpan={colCount} className="empty-section">
                          No tutors scheduled
                        </td>
                      </tr>
                    )}
                    {rows.map((t) => (
                      <tr key={t.id}>
                        <td>{t.name}</td>
                        <td>{t.start}</td>
                        <td>{t.end}</td>
                        <td>{t.course}</td>
                        {!isCompleted && (
                          <td>
                            <select
                              value={sectionToStatus[t.section]}
                              onChange={(e) =>
                                changeSection(t.id, e.target.value)
                              }
                            >
                              {opts.map((o) => (
                                <option key={o} value={o}>
                                  {o}
                                </option>
                              ))}
                            </select>
                          </td>
                        )}
                        {isActive && (
                          <td
                            className={
                              t.availability === "Full"
                                ? "status-full"
                                : "status-open"
                            }
                          >
                            <select
                              value={t.availability}
                              onChange={(e) =>
                                changeAvailability(t.id, e.target.value)
                              }
                            >
                              {ACTIVE_AVAILABILITY.map((s) => (
                                <option key={s} value={s}>
                                  {s}
                                </option>
                              ))}
                            </select>
                          </td>
                        )}
                      </tr>
                    ))}
                  </tbody>
                </table>
              </div>
            );
          })}
        </div>
      );
    }

    ReactDOM.createRoot(document.getElementById("root")).render(<App />);
  </script>
</body>

</html>