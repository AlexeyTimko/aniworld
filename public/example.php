<?$s = '< tr>
< td>2015–04–20</td>
< td>
< input class=" long-url“ onclick=" this.select ()“ value=" naslat.ru/tupe/скачать ватсап бесплатно“ name=" T2“ size="20"></td>
< td>66</td>
< td>< a href=" http://p-visit2.php?date=2015–04–20">64<...;
< td>1</td>
< td>63</td>

< td>1:0</td>
< td>
< a href=" http://p-.php?date_start=2015–04–20&date_end=2015–...;
0</a></td>

< td>12</td>
< td>3</td>
< td>2</td>
< td>6.40</td>

< td>9</td>
< td>310.00</td>
< td>1:0</td>
< td>1:5</td>
< td>4.94</td>
< td>316.4</td>
</tr>
< tr>
< td>2015–04–17</td>
< td>
< input class=" long-url“ onclick=" this.select ()“ value=" proklevguru.ru/ribos/viber скачать на телефон“ name=" T3» size="20"></td>
< td>25</td>
< td>< a href=" http://p-visit2.php?date=2015–04–17">25<...;

< td>25</td>

< td>1:0</td>
< td>
< a href=" http://p-.php?date_start=2015–04–17&date_end=2015–...;
0</a></td>

< td>9</td>

< td>9</td>
< td>270.00</td>
< td>1:0</td>
< td>1:2</td>
< td>10.8</td>
< td>270</td>
</tr>
< tr>
< td>2015–04–15</td>
< td>
< input class=" long-url“ onclick=" this.select ()“ value=" byrenie.com/doces/скачать ватсап бесплатно“ name=" T4» size="20"></td>
< td>46</td>
< td>< a href=" http://p-visit2.php?date=2015–04–15">45<...;
< td>1</td>
< td>44</td>';?>
    <form method="post">
        <textarea style="width: 300px;height: 200px;" name="Text"><?=$s;?></textarea><br/>
        <input type="submit" value="Парсить"/>
    </form>
<?if(isset($_POST['Text'])){
    $s = $_POST['Text'];
    foreach(explode("\r\n",$s) as $row){
        if(stripos($row,'input class') !== false){
            preg_match('/(?<=\/)([^\"|\“\/]+)(?=(\"|\“))/ui',$row,$res);
            print current($res)."<br/>\r\n";
        }
    }
}
