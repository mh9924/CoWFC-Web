<?php
include($_SERVER["DOCUMENT_ROOT"] . '/_site/Page.php');

final class Stats extends Page {
	private $games = array();
	private $countries = array();
	private $titles = array();
	private $sorts = array();
	private $sort;
	
	public function __construct(PageController $site) {
		$this->initStats();
		parent::__construct($site);
	}
	
	private function initStats(): void {
		$this->sorts = ['Game', 'Country'];
		$this->sort = 'Game';
		if(isset($_GET['sort']) && in_array($_GET['sort'], $this->sorts))
			$this->sort = $_GET['sort'];
		$this->games = json_decode(file_get_contents("http://localhost:9001/json"), true);
	}
	
	private function initTitles(): void {
		$this->titles = json_decode(file_get_contents($_SERVER["DOCUMENT_ROOT"] . "/_pages/Stats/games.json"), true);
	}
	
	private function initCountries(): void {
		foreach ($this->games as $game){
			foreach($game as $gameconn){
				$country = $this->getCountryFromIP(long2ip($gameconn['publicip']));
				if(!isset($this->countries[$country])) {
					$this->countries[$country] = 0;
				}
				$this->countries[$country]++;
			}
		}
	}
	
	private function buildGameTable(): void {
		$this->initTitles();
		echo "<table>";
		echo "<tr>";
		echo "<th>GameID</th>";
		echo "<th>Game</th>";
		echo "<th>Platform</th>";
		echo "<th># Online</th>";
		echo "</tr>";
		foreach ($this->games as $game=>$gameconns){
			echo "<tr>";
			echo "<td style='vertical-align: middle;width: 15%;color: #e44c65;'>{$this->titles[$game]['id']}</td>";
			echo "<td style='vertical-align: middle;width: 15%;'><img src='images/games/{$this->titles[$game]['title']}.png'></td>";
			echo "<td style='vertical-align: middle;'><img src='images/platforms/{$this->titles[$game]['platform']}.png'></td>";
			echo "<td style='vertical-align: middle;color: green;font-size: 28px;'>".count($gameconns)."</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	private function buildCountryTable(): void {
		$this->initCountries();
		echo "<table>";
		echo "<tr>";
		echo "<th>Flag</th>";
		echo "<th>Country</th>";
		echo "<th># Players</th>";
		echo "</tr>";
		foreach ($this->countries as $country=>$count){
			echo "<tr>";
			echo "<td style='vertical-align: middle;width: 20%;'><img width='50' height='50' src='images/flags/{$country}.png'></td>";
			echo "<td style='vertical-align: middle;width: 40%;'>{$country}</td>";
			echo "<td style='vertical-align: middle;color: green;font-size: 28px;'>{$count}</td>";
			echo "</tr>";
		}
		echo "</table>";
	}
	
	private function buildStatsTable(): void {
		$this->{"build{$this->sort}Table"}();
	}
	
	private function buildDropDown(): void {
		echo "<select onChange='window.location.href=this.value' style='width: 120px;'>";
		foreach ($this->sorts as $sort){
			echo "<option value='?page=stats&sort={$sort}'" . ($sort == $this->sort ? 'selected' : '') .">{$sort}</option>";
		}
		echo "</select>";
	}
	
	private function getCountryFromIP(string $ip): string {
		$info = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"), true);
		return $info['country'];
	}
	
	protected function buildPage(): void {
?>
<div id="main" class="wrapper style1">
	<div class="container">
		<header class="major">
			<h2><?php echo $this->meta_title; ?></h2>
			<p>Statistics of users currently playing CoWFC</p>
		</header>

		<!-- Content -->
			<section id="content">
				<h3>Display As: <?php $this->buildDropDown(); ?></h3>
				<p><?php $this->buildStatsTable(); ?></p>
			</section>

	</div>
</div>
<?php
	}
}
?>