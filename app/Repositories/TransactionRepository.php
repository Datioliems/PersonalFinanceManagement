<?php
namespace App\Repositories;

class TransactionRepository extends BaseRepository
{
    protected function getTable(): string { return 'transactions'; }

    // ── CRUD ──────────────────────────────────────────────────

    public function findByType(string $type, int $userId, int $limit=15, int $offset=0): array
    {
        $stmt=$this->db->prepare(
            'SELECT t.*, c.name AS category_name, c.color AS category_color
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE t.user_id=:uid AND t.type=:type
             ORDER BY t.trans_date DESC, t.id DESC LIMIT :limit OFFSET :offset'
        );
        $stmt->bindValue(':uid',$userId,\PDO::PARAM_INT);
        $stmt->bindValue(':type',$type);
        $stmt->bindValue(':limit',$limit,\PDO::PARAM_INT);
        $stmt->bindValue(':offset',$offset,\PDO::PARAM_INT);
        $stmt->execute(); return $stmt->fetchAll();
    }

    public function countByType(string $type, int $userId): int
    {
        $s=$this->db->prepare('SELECT COUNT(*) FROM transactions WHERE user_id=? AND type=?');
        $s->execute([$userId,$type]); return (int)$s->fetchColumn();
    }

    public function save(array $data): int
    {
        $s=$this->db->prepare(
            'INSERT INTO transactions (user_id,category_id,type,amount,note,trans_date)
             VALUES (:uid,:cid,:type,:amount,:note,:date)'
        );
        $s->execute([':uid'=>$data['user_id'],':cid'=>$data['category_id'],
            ':type'=>$data['type'],':amount'=>$data['amount'],
            ':note'=>$data['note']??'',':date'=>$data['trans_date']]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, int $userId, array $data): bool
    {
        $s=$this->db->prepare(
            'UPDATE transactions SET category_id=:cid,amount=:amount,note=:note,
             trans_date=:date,updated_at=NOW() WHERE id=:id AND user_id=:uid AND type=:type'
        );
        $s->execute([':cid'=>$data['category_id'],':amount'=>$data['amount'],
            ':note'=>$data['note']??'',':date'=>$data['trans_date'],
            ':id'=>$id,':uid'=>$userId,':type'=>$data['type']]);
        return $s->rowCount()>0;
    }

    public function deleteByIdAndUser(int $id, int $userId): bool
    {
        $s=$this->db->prepare('DELETE FROM transactions WHERE id=? AND user_id=?');
        $s->execute([$id,$userId]); return $s->rowCount()>0;
    }

    // ── FILTER ────────────────────────────────────────────────

    public function findFiltered(int $userId, string $type='', string $sort='date_desc',
        string $startDate='', string $endDate='',
        int $limit=15, int $offset=0, int $categoryId=0): array
    {
        $orderMap=['date_desc'=>'t.trans_date DESC, t.id DESC','date_asc'=>'t.trans_date ASC, t.id ASC',
            'amount_desc'=>'t.amount DESC','amount_asc'=>'t.amount ASC'];
        $sql='SELECT t.*, c.name AS category_name, c.color AS category_color
              FROM transactions t JOIN categories c ON t.category_id=c.id WHERE t.user_id=:uid';
        $p=[':uid'=>$userId];
        if ($type!=='')       { $sql.=' AND t.type=:type';          $p[':type']=$type; }
        if (!empty($startDate)){$sql.=' AND t.trans_date>=:start';  $p[':start']=$startDate; }
        if (!empty($endDate)) { $sql.=' AND t.trans_date<=:end';    $p[':end']=$endDate; }
        if ($categoryId>0)    { $sql.=' AND t.category_id=:cat';    $p[':cat']=$categoryId; }
        $sql.=' ORDER BY '.($orderMap[$sort]??$orderMap['date_desc']).' LIMIT :limit OFFSET :offset';
        $stmt=$this->db->prepare($sql);
        foreach($p as $k=>$v) $stmt->bindValue($k,$v);
        $stmt->bindValue(':limit',$limit,\PDO::PARAM_INT);
        $stmt->bindValue(':offset',$offset,\PDO::PARAM_INT);
        $stmt->execute(); return $stmt->fetchAll();
    }

    public function countFiltered(int $userId, string $type='', string $startDate='',
        string $endDate='', int $categoryId=0): int
    {
        $sql='SELECT COUNT(*) FROM transactions WHERE user_id=:uid'; $p=[':uid'=>$userId];
        if ($type!=='')       { $sql.=' AND type=:type';        $p[':type']=$type; }
        if (!empty($startDate)){$sql.=' AND trans_date>=:start';$p[':start']=$startDate; }
        if (!empty($endDate)) { $sql.=' AND trans_date<=:end';  $p[':end']=$endDate; }
        if ($categoryId>0)    { $sql.=' AND category_id=:cat'; $p[':cat']=$categoryId; }
        $stmt=$this->db->prepare($sql); $stmt->execute($p);
        return (int)$stmt->fetchColumn();
    }

    // ── SUMMARY — dùng bởi ReportService ─────────────────────

    public function getSummaryByMonth(int $userId, int $month, int $year): array
    {
        $s=$this->db->prepare(
            'SELECT type,COALESCE(SUM(amount),0) AS total FROM transactions
             WHERE user_id=:uid AND MONTH(trans_date)=:m AND YEAR(trans_date)=:y GROUP BY type'
        );
        $s->execute([':uid'=>$userId,':m'=>$month,':y'=>$year]);
        $r=['income'=>0.0,'expense'=>0.0];
        foreach($s->fetchAll() as $row) $r[$row['type']]=(float)$row['total'];
        return $r;
    }

    public function getSumByCategory(int $categoryId, int $userId, int $month, int $year): float
    {
        $s=$this->db->prepare(
            "SELECT COALESCE(SUM(amount),0) FROM transactions
             WHERE user_id=:uid AND category_id=:cid AND type='expense'
               AND MONTH(trans_date)=:m AND YEAR(trans_date)=:y"
        );
        $s->execute([':uid'=>$userId,':cid'=>$categoryId,':m'=>$month,':y'=>$year]);
        return (float)$s->fetchColumn();
    }

    public function getExpenseByCategory(int $userId, int $month, int $year): array
    {
        $s=$this->db->prepare(
            "SELECT c.name AS category_name, c.color, SUM(t.amount) AS total
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE t.user_id=:uid AND t.type='expense'
               AND MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y
             GROUP BY t.category_id ORDER BY total DESC"
        );
        $s->execute([':uid'=>$userId,':m'=>$month,':y'=>$year]); return $s->fetchAll();
    }

    public function findByMonth(int $userId, int $month, int $year): array
    {
        $s=$this->db->prepare(
            'SELECT t.trans_date, t.type, c.name AS category_name, t.amount, t.note
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE t.user_id=:uid AND MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y
             ORDER BY t.trans_date ASC, t.id ASC'
        );
        $s->execute([':uid'=>$userId,':m'=>$month,':y'=>$year]); return $s->fetchAll();
    }

    public function findByDateRange(int $userId, \DateTime $from, \DateTime $to): array
    {
        $s=$this->db->prepare(
            'SELECT * FROM transactions WHERE user_id=:uid AND trans_date>=:from AND trans_date<=:to ORDER BY trans_date ASC'
        );
        $s->execute([':uid'=>$userId,':from'=>$from->format('Y-m-d'),':to'=>$to->format('Y-m-d')]);
        return $s->fetchAll();
    }

    public function getSummaryByRange(int $userId, string $start, string $end): array
    {
        $s=$this->db->prepare(
            "SELECT SUM(CASE WHEN type='income' THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions WHERE user_id=:uid AND trans_date>=:start AND trans_date<=:end"
        );
        $s->execute([':uid'=>$userId,':start'=>$start,':end'=>$end]);
        return $s->fetch() ?: ['income'=>0,'expense'=>0];
    }

    public function getDailySummary(int $userId, string $startDate='', string $endDate=''): array
    {
        $sql="SELECT trans_date,
                     COUNT(*) AS total_tx,
                     SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                     SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense,
                     SUM(CASE WHEN type='income'  THEN amount ELSE -amount END) AS balance
              FROM transactions WHERE user_id=:uid"; $p=[':uid'=>$userId];
        if (!empty($startDate)){$sql.=' AND trans_date>=:start';$p[':start']=$startDate;}
        if (!empty($endDate))  {$sql.=' AND trans_date<=:end';  $p[':end']=$endDate;}
        $sql.=' GROUP BY trans_date ORDER BY trans_date DESC';
        $stmt=$this->db->prepare($sql); $stmt->execute($p); return $stmt->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    // BÁO CÁO NÂNG CAO — MỚI
    // ══════════════════════════════════════════════════════════

    /**
     * Chi tiết theo danh mục: tổng, số lần, trung bình, min, max.
     * Dùng cho tab "Chi tiết danh mục" trong report.
     *
     * @param string $type 'income' | 'expense' | '' (tất cả)
     */
    public function getDetailByCategory(int $userId, int $month, int $year, string $type='expense'): array
    {
        $sql='SELECT c.name AS category_name, c.color, c.icon,
                     SUM(t.amount) AS total, COUNT(t.id) AS tx_count,
                     AVG(t.amount) AS avg_amount,
                     MIN(t.amount) AS min_amount,
                     MAX(t.amount) AS max_amount
              FROM transactions t JOIN categories c ON t.category_id=c.id
              WHERE t.user_id=:uid AND MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y';
        $p=[':uid'=>$userId,':m'=>$month,':y'=>$year];
        if ($type!=='') { $sql.=' AND t.type=:type'; $p[':type']=$type; }
        $sql.=' GROUP BY t.category_id ORDER BY total DESC';
        $stmt=$this->db->prepare($sql); $stmt->execute($p); return $stmt->fetchAll();
    }

    /**
     * Tổng hợp cả năm — đủ 12 tháng (tháng không có giao dịch = 0).
     * Dùng cho tab "Doanh thu theo năm".
     */
    public function getYearlySummary(int $userId, int $year): array
    {
        $s=$this->db->prepare(
            "SELECT MONTH(trans_date) AS month,
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions WHERE user_id=:uid AND YEAR(trans_date)=:y
             GROUP BY MONTH(trans_date)"
        );
        $s->execute([':uid'=>$userId,':y'=>$year]);
        $byMonth=array_column($s->fetchAll(),null,'month');
        $result=[];
        for ($m=1;$m<=12;$m++) {
            $inc=(float)($byMonth[$m]['income']??0);
            $exp=(float)($byMonth[$m]['expense']??0);
            $result[]=['month'=>$m,'income'=>$inc,'expense'=>$exp,'balance'=>$inc-$exp];
        }
        return $result;
    }

    /**
     * So sánh nhiều tháng với nhau.
     * @param int[] $months VD: [1,2,3,4]
     */
    public function getMonthlyComparison(int $userId, array $months, int $year): array
    {
        if (empty($months)) return ['labels'=>[],'income'=>[],'expense'=>[]];
        $ph=implode(',',array_fill(0,count($months),'?'));
        $s=$this->db->prepare(
            "SELECT MONTH(trans_date) AS month,
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions
             WHERE user_id=? AND YEAR(trans_date)=? AND MONTH(trans_date) IN ({$ph})
             GROUP BY MONTH(trans_date)"
        );
        $s->execute(array_merge([$userId,$year],$months));
        $bm=array_column($s->fetchAll(),null,'month');
        $labels=$income=$expense=[];
        foreach ($months as $m) {
            $labels[]="Tháng {$m}";
            $income[] =(float)($bm[$m]['income'] ??0);
            $expense[]=(float)($bm[$m]['expense']??0);
        }
        return compact('labels','income','expense');
    }

    /**
     * Giao dịch của 1 danh mục trong tháng — cho modal chi tiết.
     */
    public function findByCategoryAndMonth(int $userId, int $categoryId, int $month, int $year, string $type=''): array
    {
        $sql='SELECT t.*, c.name AS category_name FROM transactions t
              JOIN categories c ON t.category_id=c.id
              WHERE t.user_id=:uid AND t.category_id=:cid
                AND MONTH(t.trans_date)=:m AND YEAR(t.trans_date)=:y';
        $p=[':uid'=>$userId,':cid'=>$categoryId,':m'=>$month,':y'=>$year];
        if ($type!=='') { $sql.=' AND t.type=:type'; $p[':type']=$type; }
        $sql.=' ORDER BY t.trans_date DESC';
        $stmt=$this->db->prepare($sql); $stmt->execute($p); return $stmt->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    // BÁO CÁO THEO KỲ TÙY CHỌN (date-range)
    // ══════════════════════════════════════════════════════════

    /**
     * Tổng hợp thu/chi trong kỳ (dateFrom → dateTo).
     * (alias với getSummaryByRange, giữ signature nhất quán)
     */
    public function getSummaryByDateRange(int $userId, string $dateFrom, string $dateTo): array
    {
        $s = $this->db->prepare(
            "SELECT SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions
             WHERE user_id=:uid AND trans_date>=:from AND trans_date<=:to"
        );
        $s->execute([':uid' => $userId, ':from' => $dateFrom, ':to' => $dateTo]);
        $r = $s->fetch() ?: ['income' => 0, 'expense' => 0];
        return ['income' => (float)$r['income'], 'expense' => (float)$r['expense']];
    }

    /**
     * Số dư ví lũy kế từ đầu đến ngày dateTo (tất cả lịch sử).
     * Công thức: SUM(income) - SUM(expense) WHERE trans_date <= dateTo
     */
    public function getWalletBalanceUpTo(int $userId, string $dateTo): float
    {
        $s = $this->db->prepare(
            "SELECT SUM(CASE WHEN type='income' THEN amount ELSE -amount END)
             FROM transactions WHERE user_id=:uid AND trans_date<=:to"
        );
        $s->execute([':uid' => $userId, ':to' => $dateTo]);
        return (float)($s->fetchColumn() ?? 0);
    }

    /**
     * Thu hoặc chi theo danh mục trong kỳ.
     * @param string $type 'income' | 'expense'
     */
    public function getByCategoryByRange(int $userId, string $dateFrom, string $dateTo, string $type): array
    {
        $s = $this->db->prepare(
            "SELECT t.category_id, c.name AS category_name, c.color, c.icon,
                    SUM(t.amount) AS total, COUNT(t.id) AS tx_count
             FROM transactions t JOIN categories c ON t.category_id=c.id
             WHERE t.user_id=:uid AND t.type=:type
               AND t.trans_date>=:from AND t.trans_date<=:to
             GROUP BY t.category_id ORDER BY total DESC"
        );
        $s->execute([':uid' => $userId, ':type' => $type, ':from' => $dateFrom, ':to' => $dateTo]);
        return $s->fetchAll();
    }

    /**
     * Tổng thu/chi theo từng ngày trong kỳ.
     * Trả về array ngày (YYYY-MM-DD) => ['income'=>..., 'expense'=>...]
     */
    public function getDailyTotals(int $userId, string $dateFrom, string $dateTo): array
    {
        $s = $this->db->prepare(
            "SELECT trans_date,
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions
             WHERE user_id=:uid AND trans_date>=:from AND trans_date<=:to
             GROUP BY trans_date ORDER BY trans_date ASC"
        );
        $s->execute([':uid' => $userId, ':from' => $dateFrom, ':to' => $dateTo]);
        $result = [];
        foreach ($s->fetchAll() as $row) {
            $result[$row['trans_date']] = [
                'income'  => (float)$row['income'],
                'expense' => (float)$row['expense'],
            ];
        }
        return $result;
    }

    /**
     * Tổng thu/chi theo tuần (ISO week) trong kỳ.
     * Trả về array week_label => ['income'=>..., 'expense'=>...]
     */
    public function getWeeklyTotals(int $userId, string $dateFrom, string $dateTo): array
    {
        $s = $this->db->prepare(
            "SELECT YEARWEEK(trans_date, 1) AS yw,
                    MIN(trans_date) AS week_start,
                    MAX(trans_date) AS week_end,
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions
             WHERE user_id=:uid AND trans_date>=:from AND trans_date<=:to
             GROUP BY YEARWEEK(trans_date, 1) ORDER BY yw ASC"
        );
        $s->execute([':uid' => $userId, ':from' => $dateFrom, ':to' => $dateTo]);
        $result = [];
        foreach ($s->fetchAll() as $row) {
            $label = date('d/m', strtotime($row['week_start'])) . '–' . date('d/m', strtotime($row['week_end']));
            $result[$row['yw']] = [
                'label'   => $label,
                'income'  => (float)$row['income'],
                'expense' => (float)$row['expense'],
            ];
        }
        return $result;
    }

    /**
     * Tổng thu/chi theo tháng trong kỳ.
     * Trả về array 'YYYY-MM' => ['income'=>..., 'expense'=>...]
     */
    public function getMonthlyTotals(int $userId, string $dateFrom, string $dateTo): array
    {
        $s = $this->db->prepare(
            "SELECT DATE_FORMAT(trans_date, '%Y-%m') AS ym,
                    SUM(CASE WHEN type='income'  THEN amount ELSE 0 END) AS income,
                    SUM(CASE WHEN type='expense' THEN amount ELSE 0 END) AS expense
             FROM transactions
             WHERE user_id=:uid AND trans_date>=:from AND trans_date<=:to
             GROUP BY ym ORDER BY ym ASC"
        );
        $s->execute([':uid' => $userId, ':from' => $dateFrom, ':to' => $dateTo]);
        $result = [];
        foreach ($s->fetchAll() as $row) {
            $result[$row['ym']] = [
                'income'  => (float)$row['income'],
                'expense' => (float)$row['expense'],
            ];
        }
        return $result;
    }

    /**
     * Lũy kế số dư trước ngày dateFrom (để vẽ rolling balance từ đầu kỳ).
     */
    public function getBalanceBeforeDate(int $userId, string $dateFrom): float
    {
        $s = $this->db->prepare(
            "SELECT SUM(CASE WHEN type='income' THEN amount ELSE -amount END)
             FROM transactions WHERE user_id=:uid AND trans_date < :from"
        );
        $s->execute([':uid' => $userId, ':from' => $dateFrom]);
        return (float)($s->fetchColumn() ?? 0);
    }
}