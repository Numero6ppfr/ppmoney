<?php /*
	Copyright 2015 Cédric Levieux, Parti Pirate

	This file is part of PPMoney.

    PPMoney is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    PPMoney is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with PPMoney.  If not, see <http://www.gnu.org/licenses/>.
*/

class ProjectBo {
	var $pdo = null;

	function __construct($pdo) {
		$this->pdo = $pdo;
	}

	static function newInstance($pdo) {
		return new ProjectBo($pdo);
	}

	function create(&$transaction) {
		$query = "	INSERT INTO transactions () VALUES ()	";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		try {
			$statement->execute();
			$transaction["tra_id"] = $this->pdo->lastInsertId();

			return true;
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return false;
	}

	function update($transaction) {
		$query = "	UPDATE transactions SET ";

		$separator = "";
		foreach($transaction as $field => $value) {
			$query .= $separator;
			$query .= $field . " = :". $field;
			$separator = ", ";
		}

		$query .= "	WHERE tra_id = :tra_id ";

//		echo showQuery($query, $transaction);

		$statement = $this->pdo->prepare($query);
		$statement->execute($transaction);
	}

	function save(&$transaction) {
		if (!isset($transaction["tra_id"]) || !$transaction["tra_id"]) {
			$this->create($transaction);

			// create reference
			$transaction["tra_reference"] = "" . $transaction["tra_id"];
			while(strlen($transaction["tra_reference"]) < 8) {
				$transaction["tra_reference"] = "0" . $transaction["tra_reference"];
			}
			$transaction["tra_reference"] = "PP" . $transaction["tra_reference"];
		}

		$this->update($transaction);
	}

	function getProjects($filters) {
		$args = array();
		$projects = array();

		$query = "	SELECT *
					FROM
						projects
					LEFT JOIN counter_parties
						ON cpa_project_id = pro_id
					WHERE
						1 = 1 \n";

		// 		if (isset($filters["tra_status"])) {
		// 			$args["tra_status"] = $filters["tra_status"];
		// 			$query .= " AND tra_status = :tra_status \n";
		// 		}

		// 		if (isset($filters["tra_confirmed"])) {
		// 			$args["tra_confirmed"] = $filters["tra_confirmed"];
		// 			$query .= " AND tra_confirmed = :tra_confirmed \n";
		// 		}

		// 		if (isset($filters["tra_from_date"])) {
		// 			$args["tra_from_date"] = $filters["tra_from_date"];
		// 			$query .= " AND tra_date > :tra_from_date \n";
		// 		}

		// 		if (isset($filters["tra_to_date"])) {
		// 			$args["tra_to_date"] = $filters["tra_to_date"];
		// 			$query .= " AND DATE_FORMAT(tra_date, '%Y-%m-%d') <= :tra_to_date \n";
		// 		}

		// 		if (isset($filters["tra_like_purpose"])) {
		// 			$args["tra_like_purpose"] = $filters["tra_like_purpose"];
		// 			$query .= "AND tra_purpose LIKE :tra_like_purpose \n";
		// 		}

		$query .= "	ORDER BY pro_id ASC, cpa_amount ASC ";

		$statement = $this->pdo->prepare($query);
		//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			foreach($results as $line) {
				$projects[$line["pro_id"]]["pro_id"] = $line["pro_id"];
				$projects[$line["pro_id"]]["pro_label"] = utf8_encode($line["pro_label"]);
				$projects[$line["pro_id"]]["pro_code"] = $line["pro_code"];
				$projects[$line["pro_id"]]["pro_content"] = utf8_encode($line["pro_content"]);
				$projects[$line["pro_id"]]["pro_amount_goal"] = $line["pro_amount_goal"];
				$projects[$line["pro_id"]]["pro_status"] = $line["pro_status"];

				if (!isset($projects[$line["pro_id"]]["counterparties"])) {
					$projects[$line["pro_id"]]["counterparties"] = array();
				}

				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_id"] = $line["cpa_id"];
				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_amount"] = $line["cpa_amount"];
				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_content"] = utf8_encode($line["cpa_content"]);
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return $projects;
	}

	function getProject($projectId) {
		$args = array("pro_id" => $projectId);

		$query = "	SELECT *
					FROM
						projects
					LEFT JOIN counter_parties
						ON cpa_project_id = pro_id
					WHERE
						pro_id = :pro_id \n";

// 		if (isset($filters["tra_status"])) {
// 			$args["tra_status"] = $filters["tra_status"];
// 			$query .= " AND tra_status = :tra_status \n";
// 		}

// 		if (isset($filters["tra_confirmed"])) {
// 			$args["tra_confirmed"] = $filters["tra_confirmed"];
// 			$query .= " AND tra_confirmed = :tra_confirmed \n";
// 		}

// 		if (isset($filters["tra_from_date"])) {
// 			$args["tra_from_date"] = $filters["tra_from_date"];
// 			$query .= " AND tra_date > :tra_from_date \n";
// 		}

// 		if (isset($filters["tra_to_date"])) {
// 			$args["tra_to_date"] = $filters["tra_to_date"];
// 			$query .= " AND DATE_FORMAT(tra_date, '%Y-%m-%d') <= :tra_to_date \n";
// 		}

// 		if (isset($filters["tra_like_purpose"])) {
// 			$args["tra_like_purpose"] = $filters["tra_like_purpose"];
// 			$query .= "AND tra_purpose LIKE :tra_like_purpose \n";
// 		}

		$query .= "	ORDER BY pro_id ASC, cpa_amount ASC ";

		$statement = $this->pdo->prepare($query);
//		echo showQuery($query, $args);

		try {
			$statement->execute($args);
			$results = $statement->fetchAll();

			$projects = array();

			foreach($results as $line) {
				$projects[$line["pro_id"]]["pro_id"] = $line["pro_id"];
				$projects[$line["pro_id"]]["pro_label"] = utf8_encode($line["pro_label"]);
				$projects[$line["pro_id"]]["pro_code"] = $line["pro_code"];
				$projects[$line["pro_id"]]["pro_content"] = utf8_encode($line["pro_content"]);
				$projects[$line["pro_id"]]["pro_amount_goal"] = $line["pro_amount_goal"];
				$projects[$line["pro_id"]]["pro_status"] = $line["pro_status"];

				if (!isset($projects[$line["pro_id"]]["counterparties"])) {
					$projects[$line["pro_id"]]["counterparties"] = array();
				}

				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_id"] = $line["cpa_id"];
				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_amount"] = $line["cpa_amount"];
				$projects[$line["pro_id"]]["counterparties"][$line["cpa_id"]]["cpa_content"] = utf8_encode($line["cpa_content"]);
			}

			if (count($projects)) {
				return $projects[$projectId];
			}
		}
		catch(Exception $e){
			echo 'Erreur de requète : ', $e->getMessage();
		}

		return null;
	}
}