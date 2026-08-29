<?php
namespace Tests\Feature\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

trait FinalTestData
{
    protected function token(string $p): string { return $p.'_'.str_replace('.','',uniqid('',true)); }

    protected function user(string $role='learner', array $x=[]): int {
        $t=$this->token($role);
        return (int) DB::table('users')->insertGetId(array_merge([
            'full_name'=>'MindHub Test','email'=>$t.'@example.test','phone'=>null,
            'password_hash'=>Hash::make('Password123!'),'role'=>$role,'status'=>'active',
            'locked'=>0,'locked_reason'=>null,'email_verified_at'=>now(),'last_login_at'=>null,
            'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function category(array $x=[]): int {
        $t=$this->token('cat');
        return (int) DB::table('categories')->insertGetId(array_merge([
            'parent_id'=>null,'name'=>'Danh mục '.$t,'slug'=>$t,'description'=>'FINAL test',
            'sort_order'=>0,'status'=>'active','created_at'=>now(),'updated_at'=>now()
        ],$x));
    }
    protected function course(int $instructor, array $x=[]): int {
        $t=$this->token('course');
        return (int) DB::table('courses')->insertGetId(array_merge([
            'instructor_id'=>$instructor,'title'=>'Khóa học '.$t,'slug'=>$t,
            'short_description'=>'FINAL test','description'=>'Fixture tự sinh',
            'thumbnail_url'=>null,'thumbnail_public_id'=>null,'intro_video_url'=>null,'intro_video_id'=>null,
            'price'=>500000,'sale_price'=>null,'course_level'=>'beginner','language'=>'vi',
            'requirements'=>json_encode([]),'outcomes'=>json_encode([]),'status'=>'published',
            'is_featured'=>0,'published_at'=>now(),'reviewed_by'=>null,'admin_reject_reason'=>null,
            'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function rule(array $x=[]): int {
        return (int) DB::table('commission_rules')->insertGetId(array_merge([
            'name'=>'80/20 '.$this->token('rule'),'description'=>'FINAL test',
            'instructor_rate'=>0.8,'platform_rate'=>0.2,'is_active'=>0,
            'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function order(int $user,int $course,int $rule,array $x=[]): int {
        return (int) DB::table('orders')->insertGetId(array_merge([
            'user_id'=>$user,'course_id'=>$course,'coupon_id'=>null,'commission_rule_id'=>$rule,
            'order_code'=>'MH'.now()->format('YmdHis').random_int(100000,999999),
            'price_snapshot'=>500000,'discount_amount'=>0,'amount'=>500000,'payment_method'=>null,
            'status'=>'pending_payment','payment_status'=>'pending','paid_at'=>null,'failed_reason'=>null,
            'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function revenue(int $ins,int $course,int $order,int $rule,array $x=[]): int {
        return (int) DB::table('revenues')->insertGetId(array_merge([
            'instructor_id'=>$ins,'course_id'=>$course,'order_id'=>$order,
            'gross_amount'=>500000,'instructor_amount'=>400000,'platform_fee_amount'=>100000,
            'commission_rule_id'=>$rule,'earned_at'=>now(),'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function payout(int $user,array $x=[]): int {
        return (int) DB::table('payout_accounts')->insertGetId(array_merge([
            'user_id'=>$user,'provider'=>'VCB','account_number'=>'0123456789','account_name'=>'MINDHUB TEST',
            'status'=>'verified','is_default'=>0,'verified_at'=>now(),'disabled_at'=>null,
            'created_at'=>now(),'updated_at'=>now(),
        ],$x));
    }
    protected function enums(string $table,string $column): array {
        $r=DB::selectOne("SHOW COLUMNS FROM `{$table}` LIKE ?",[$column]);
        preg_match_all("/'([^']+)'/",$r->Type ?? '',$m); return $m[1] ?? [];
    }
}
