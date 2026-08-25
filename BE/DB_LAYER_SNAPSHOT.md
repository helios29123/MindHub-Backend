# DB LAYER SNAPSHOT

Generated: 2026-08-25 22:07:16
Project root: D:\laragon\www\datn\MindHub-Backend\BE

> File này chỉ dùng để audit/đối chiếu. Script không sửa Migration hoặc Model.


# MIGRATIONS

Total files: 28

---

## FILE: database\migrations\2026_08_25_141737_create_users_table.php

SHA256: 24EE09046C5A45B53CFFCDCB6AB87FB508434A7706A8CE9825B4671EFCFABAD2

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('full_name', 150);
            $table->string('email', 255)->unique('uq_users_email');
            $table->string('phone', 30)->nullable()->unique('uq_users_phone');
            $table->string('password_hash', 255);
            $table->string('avatar_url', 2048)->nullable();
            $table->string('avatar_public_id', 255)->nullable()->unique('uq_users_avatar_public_id');
            $table->enum('role', ['learner', 'instructor', 'admin'])->default('learner');
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->boolean('locked')->default(false);
            $table->string('locked_reason', 500)->nullable();
            $table->dateTime('email_verified_at')->nullable();
            $table->dateTime('last_login_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['role', 'status'], 'idx_users_role_status');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141738_create_banners_table.php

SHA256: 0CDB0C38CED8BB0FF383EDC201F41CF689AF993924C3EE8D8B1952E34C3BEE7E

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('banners', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('title', 255);
            $table->string('image_url', 2048);
            $table->string('image_public_id', 255)->nullable()->unique('uq_banners_image_public_id');
            $table->string('target_url', 2048)->nullable();
            $table->string('position', 100);
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['position', 'status', 'sort_order'], 'idx_banners_position_status_order');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141738_create_categories_table.php

SHA256: DDF3DE1341D0D8EFFDDD090745189CF01DDCBB147E06EF8C741A3DB87C596FF8

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 150);
            $table->string('slug', 180)->unique('uq_categories_slug');
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('parent_id', 'idx_categories_parent');
            $table->foreign('parent_id', 'fk_categories_parent')->references('id')->on('categories')->nullOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_categories_two_levels_bi`
BEFORE INSERT ON `categories`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `categories`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Categories are limited to two levels';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_categories_two_levels_bu`
BEFORE UPDATE ON `categories`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `categories`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Categories are limited to two levels';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_categories_two_levels_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_categories_two_levels_bi`');
        Schema::dropIfExists('categories');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141739_create_commission_rules_table.php

SHA256: 4BDEC6140C275A3891EFD2E6EC3880B6407082A6FE66CBEA2523052FE839F0C6

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commission_rules', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('name', 150);
            $table->string('description', 1000)->nullable();
            $table->decimal('instructor_rate', 5, 4);
            $table->decimal('platform_rate', 5, 4);
            $table->boolean('is_active')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('is_active', 'idx_commission_rules_active');
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_commission_rules_one_active_bi`
BEFORE INSERT ON `commission_rules`
FOR EACH ROW
BEGIN
    IF NEW.is_active = 1
       AND EXISTS (SELECT 1 FROM `commission_rules` WHERE `is_active` = 1)
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_commission_rules_one_active_bu`
BEFORE UPDATE ON `commission_rules`
FOR EACH ROW
BEGIN
    IF NEW.is_active = 1
       AND EXISTS (
            SELECT 1
            FROM `commission_rules`
            WHERE `is_active` = 1
              AND `id` <> OLD.`id`
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only one commission rule can be active at a time';
    END IF;

    IF (NEW.instructor_rate <> OLD.instructor_rate
        OR NEW.platform_rate <> OLD.platform_rate)
       AND (
            EXISTS (SELECT 1 FROM `orders` WHERE `commission_rule_id` = OLD.`id`)
            OR EXISTS (SELECT 1 FROM `revenues` WHERE `commission_rule_id` = OLD.`id`)
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Referenced commission rule rates are immutable; create a new rule instead';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_commission_rules_one_active_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_commission_rules_one_active_bi`');
        Schema::dropIfExists('commission_rules');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141739_create_courses_table.php

SHA256: 94506D0DFB67B28D9624FD387076C242930090CD7E083E588B06C9340472088F

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->string('title', 255);
            $table->string('slug', 255)->unique('uq_courses_slug');
            $table->string('short_description', 500)->nullable();
            $table->longText('description')->nullable();
            $table->string('thumbnail_url', 2048)->nullable();
            $table->string('thumbnail_public_id', 255)->nullable()->unique('uq_courses_thumbnail_public_id');
            $table->string('intro_video_url', 2048)->nullable();
            $table->string('intro_video_id', 255)->nullable()->unique('uq_courses_intro_video_id');
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->enum('course_level', ['beginner', 'intermediate', 'advanced', 'all_levels'])->default('beginner');
            $table->string('language', 20)->default('vi');
            $table->json('requirements')->nullable();
            $table->json('outcomes')->nullable();
            $table->enum('status', ['draft', 'pending_review', 'approved', 'rejected', 'published', 'hidden'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->dateTime('published_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->string('admin_reject_reason', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            $table->decimal('sale_price', 15, 2)->storedAs('ROUND((price * (1 - (discount_percent / 100))), 2)');
            
            $table->index(['instructor_id', 'status'], 'idx_courses_instructor_status');
            $table->index(['is_featured', 'status'], 'idx_courses_featured');
            $table->index('reviewed_by', 'idx_courses_reviewed_by');
            $table->foreign('instructor_id', 'fk_courses_instructor')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('reviewed_by', 'fk_courses_reviewed_by')->references('id')->on('users')->nullOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141740_create_instructor_profiles_table.php

SHA256: 32B07A45E12BBB96663B6E2D4C0F3774BCD97D8018BBDADC126121CC1D3AE5D5

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('instructor_profiles', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id')->unique('uq_instructor_profiles_user');
            $table->text('bio')->nullable();
            $table->string('expertise', 500)->nullable();
            $table->unsignedSmallInteger('experience_years')->default(0);
            $table->enum('instructor_rank', ['bronze', 'silver', 'gold', 'diamond'])->default('bronze');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('user_id', 'fk_instructor_profiles_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('instructor_profiles');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141741_create_coupons_table.php

SHA256: EB4225165D17048EB35481B0D9AB698F0838CEB50CB0D200D76DE067041FA49B

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('code', 80)->unique('uq_coupons_code');
            $table->unsignedBigInteger('course_id')->unique('uq_coupons_course');
            $table->enum('discount_type', ['percent', 'fixed']);
            $table->decimal('discount_value', 15, 2);
            $table->unsignedInteger('usage_limit')->nullable();
            $table->unsignedInteger('used_count')->default(0);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->enum('status', ['active', 'inactive', 'expired', 'used_up'])->default('active');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['course_id', 'status'], 'idx_coupons_course_status');
            $table->foreign('course_id', 'fk_coupons_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141741_create_course_sections_table.php

SHA256: 86A654F9B647F00A509DF48227ECDD186583C58AD9124651F5FB9BADD74C945B

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_sections', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('course_id');
            $table->string('title', 255);
            $table->text('description')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft');
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['course_id', 'sort_order'], 'uq_course_sections_order');
            $table->unique(['id', 'course_id'], 'uq_course_sections_id_course');
            $table->foreign('course_id', 'fk_course_sections_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_sections');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141742_create_course_categories_table.php

SHA256: FD4E1BD15DCE5FA2011B7DC99BA80C1CD4CFB8B9AE25AF3548C3BF8223A0D664

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_categories', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('category_id');
            $table->primary(['course_id', 'category_id']);
            $table->index(['category_id', 'course_id'], 'idx_course_categories_category');
            $table->foreign('category_id', 'fk_course_categories_category')->references('id')->on('categories')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_course_categories_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_categories');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141742_create_lessons_table.php

SHA256: 240CF8A84C279541EFA1C1CBD65BB4501C0BC5F182A661A3FA5CBEA9D1E1EC3E

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lessons', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('course_section_id');
            $table->unsignedBigInteger('course_id');
            $table->string('title', 255);
            $table->enum('lesson_type', ['video', 'text', 'document'])->default('video');
            $table->longText('content')->nullable();
            $table->string('video_url', 2048)->nullable();
            $table->string('video_id', 255)->nullable()->unique('uq_lessons_video_id');
            $table->unsignedInteger('video_duration_seconds')->default(0);
            $table->boolean('is_preview')->default(false);
            $table->enum('status', ['draft', 'published', 'hidden'])->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['course_section_id', 'sort_order'], 'uq_lessons_section_order');
            $table->index('course_id', 'idx_lessons_course');
            $table->index(['course_section_id', 'course_id'], 'fk_lessons_section_course');
            $table->foreign(['course_section_id', 'course_id'], 'fk_lessons_section_course')->references(['id', 'course_id'])->on('course_sections')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lessons');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141743_create_faqs_table.php

SHA256: 76C2C0C64820D4DE73136168EEBFA90854FC11AD43FEF75267C972F0C7CBA044

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->string('question', 1000);
            $table->text('answer');
            $table->string('type', 100)->default('general');
            $table->enum('source', ['system', 'instructor'])->default('system');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['type', 'status', 'sort_order'], 'idx_faqs_type_status_order');
            $table->index(['source', 'type', 'status'], 'idx_faqs_source_type_status');
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141744_create_course_faqs_table.php

SHA256: 01329709E2E7AD2522422708182CB9C0FD90DE7DE4F7A9546B48F358D7CE47E6

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_faqs', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('faq_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedInteger('sort_order')->default(0);
            $table->primary(['faq_id', 'course_id']);
            $table->index(['course_id', 'sort_order'], 'idx_course_faqs_course_order');
            $table->foreign('course_id', 'fk_course_faqs_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('faq_id', 'fk_course_faqs_faq')->references('id')->on('faqs')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_faqs');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141744_create_orders_table.php

SHA256: 66F455D01A3CA03E0A05B1D8A7F179CD5933992D5C8EE8B0616D3BB822C62F6D

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->string('order_code', 80)->unique('uq_orders_order_code');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('coupon_id')->nullable();
            $table->unsignedBigInteger('commission_rule_id');
            $table->enum('status', ['pending_payment', 'paid', 'cancelled', 'failed', 'expired'])->default('pending_payment');
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'expired'])->default('pending');
            $table->decimal('price_snapshot', 15, 2);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('provider_transaction_id', 191)->nullable()->unique('uq_orders_provider_transaction');
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('expires_at')->nullable();
            $table->string('cancelled_reason', 1000)->nullable();
            $table->string('failed_reason', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'status'], 'idx_orders_user_status');
            $table->index('course_id', 'idx_orders_course');
            $table->index('coupon_id', 'idx_orders_coupon');
            $table->index('commission_rule_id', 'idx_orders_commission_rule');
            $table->index(['payment_status', 'created_at'], 'idx_orders_payment_status');
            $table->foreign('commission_rule_id', 'fk_orders_commission_rule')->references('id')->on('commission_rules')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('coupon_id', 'fk_orders_coupon')->references('id')->on('coupons')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_orders_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_orders_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141745_create_course_reviews_table.php

SHA256: B7C352BA6473E83454AA5514F24FA20E68477D857832E7226F3A4BEEB73E8879

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_reviews', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('order_id')->unique('uq_course_reviews_order');
            $table->unsignedTinyInteger('rating');
            $table->text('comment')->nullable();
            $table->dateTime('edited_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->foreign('order_id', 'fk_course_reviews_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('course_reviews');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141745_create_enrollments_table.php

SHA256: 8BCE7CC76CB83D54C705441FBBE5E9E445DC76A9094748CC59E49746C4FE0C31

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enrollments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('order_id')->unique('uq_enrollments_order');
            $table->enum('status', ['active', 'completed', 'inactive'])->default('active');
            $table->decimal('progress_percent', 5, 2)->default(0);
            $table->dateTime('enrolled_at')->useCurrent();
            $table->dateTime('expires_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->dateTime('last_accessed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['user_id', 'course_id'], 'uq_enrollments_user_course');
            $table->index(['course_id', 'status'], 'idx_enrollments_course_status');
            $table->foreign('course_id', 'fk_enrollments_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id', 'fk_enrollments_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_enrollments_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('enrollments');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141745_create_revenues_table.php

SHA256: 48341C01CC5950D1542F5F6D691918E4CC6E6834C30B579F1C45DDBAF4D45D56

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('revenues', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('instructor_id');
            $table->unsignedBigInteger('course_id');
            $table->unsignedBigInteger('order_id')->unique('uq_revenues_order');
            $table->decimal('gross_amount', 15, 2);
            $table->decimal('instructor_amount', 15, 2);
            $table->decimal('platform_fee_amount', 15, 2);
            $table->unsignedBigInteger('commission_rule_id');
            $table->dateTime('earned_at')->useCurrent();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('instructor_id', 'idx_revenues_instructor_status');
            $table->index('course_id', 'idx_revenues_course');
            $table->index('commission_rule_id', 'idx_revenues_commission_rule');
            $table->foreign('commission_rule_id', 'fk_revenues_commission_rule')->references('id')->on('commission_rules')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('course_id', 'fk_revenues_course')->references('id')->on('courses')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('instructor_id', 'fk_revenues_instructor')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('order_id', 'fk_revenues_order')->references('id')->on('orders')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('revenues');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141746_create_lesson_assets_table.php

SHA256: 0BFC9E884244EBA7B0512BDC68F87017FAE6108C4E2450D91334AD0BCD9159E6

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_assets', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('lesson_id');
            $table->string('title', 255);
            $table->string('file_url', 2048);
            $table->string('file_id', 255)->nullable()->unique('uq_lesson_assets_file_id');
            $table->string('file_name', 255);
            $table->string('file_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->string('note', 1000)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('lesson_id', 'idx_lesson_assets_lesson');
            $table->foreign('lesson_id', 'fk_lesson_assets_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_assets');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141747_create_lesson_progress_table.php

SHA256: 2BBA551211918B31E331F0CD85BD4EB4E0A5E1B9E9DD178AE13AF0CAC8AF90C1

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_progress', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('lesson_id');
            $table->enum('status', ['not_started', 'in_progress', 'completed'])->default('not_started');
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->unsignedInteger('learning_duration_seconds')->default(0);
            $table->dateTime('last_accessed_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['enrollment_id', 'lesson_id'], 'uq_lesson_progress_enrollment_lesson');
            $table->index('lesson_id', 'idx_lesson_progress_lesson');
            $table->foreign('enrollment_id', 'fk_lesson_progress_enrollment')->references('id')->on('enrollments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_lesson_progress_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_progress');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141747_create_video_progress_table.php

SHA256: 7C99A0DF35490E56F5D654CEE03A57839A3F630B0DB91CB25998D0D0522C1D90

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_progress', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('lesson_id');
            $table->unsignedInteger('current_second')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['enrollment_id', 'lesson_id'], 'uq_video_progress_enrollment_lesson');
            $table->index('lesson_id', 'idx_video_progress_lesson');
            $table->foreign('enrollment_id', 'fk_video_progress_enrollment')->references('id')->on('enrollments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_video_progress_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('video_progress');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141748_create_comments_table.php

SHA256: D89C21600DB2E389CD81E33819C779DD5A340CC2BC1467A41EA26F72C64C5F21

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->unsignedBigInteger('enrollment_id')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('lesson_id');
            $table->text('content');
            $table->enum('status', ['visible', 'hidden', 'deleted'])->default('visible');
            $table->boolean('is_official')->default(false);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index('parent_id', 'idx_comments_parent');
            $table->index('enrollment_id', 'idx_comments_enrollment');
            $table->index('user_id', 'idx_comments_user');
            $table->index(['lesson_id', 'status', 'created_at'], 'idx_comments_lesson_status');
            $table->foreign('enrollment_id', 'fk_comments_enrollment')->references('id')->on('enrollments')->nullOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_comments_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('parent_id', 'fk_comments_parent')->references('id')->on('comments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_comments_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_comments_reply_one_level_bi`
BEFORE INSERT ON `comments`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `comments`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Replies are limited to one level';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_comments_reply_one_level_bu`
BEFORE UPDATE ON `comments`
FOR EACH ROW
BEGIN
    IF NEW.parent_id IS NOT NULL
       AND EXISTS (
            SELECT 1
            FROM `comments`
            WHERE id = NEW.parent_id
              AND parent_id IS NOT NULL
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Replies are limited to one level';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_comments_reply_one_level_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_comments_reply_one_level_bi`');
        Schema::dropIfExists('comments');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141748_create_lesson_notes_table.php

SHA256: 09627ABD1CF41325542DA0A0B469E508F3384DCE576D3C6690C2C678F60BB848

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_notes', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('enrollment_id');
            $table->unsignedBigInteger('lesson_id');
            $table->text('content');
            $table->unsignedInteger('note_time_second')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['enrollment_id', 'lesson_id'], 'idx_lesson_notes_enrollment_lesson');
            $table->index('lesson_id', 'fk_lesson_notes_lesson');
            $table->foreign('enrollment_id', 'fk_lesson_notes_enrollment')->references('id')->on('enrollments')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('lesson_id', 'fk_lesson_notes_lesson')->references('id')->on('lessons')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_notes');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141749_create_notifications_table.php

SHA256: 9C02D6E5D013AC886F31DF9C850A93A3457B80137B89A7BC966949EBC98CFE42

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('type', 100);
            $table->string('title', 255);
            $table->text('message');
            $table->json('data')->nullable();
            $table->string('action_url', 2048)->nullable();
            $table->enum('channel', ['web', 'email', 'both'])->default('web');
            $table->dateTime('read_at')->nullable();
            $table->enum('email_status', ['pending', 'sent', 'failed', 'skipped'])->nullable();
            $table->dateTime('email_sent_at')->nullable();
            $table->text('email_error')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'read_at', 'created_at'], 'idx_notifications_user_read');
            $table->index('email_status', 'idx_notifications_email_status');
            $table->foreign('user_id', 'fk_notifications_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141749_create_wishlist_table.php

SHA256: 23FB1BA284A72CAA1108D34232CA8C0CFB056E41D168FEDB12EB6B723326D33B

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wishlist', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('course_id');
            $table->dateTime('created_at')->useCurrent();
            $table->primary(['user_id', 'course_id']);
            $table->index('course_id', 'idx_wishlist_course');
            $table->foreign('course_id', 'fk_wishlist_course')->references('id')->on('courses')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_wishlist_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('wishlist');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141750_create_sessions_table.php

SHA256: F63762E1F00201F7C947F07824E7883D644537619543352C7E9CFDB6FF79674C

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sessions', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('refresh_token_hash', 255)->unique('uq_sessions_refresh_token_hash');
            $table->string('device_name', 255)->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->dateTime('expires_at');
            $table->dateTime('revoked_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'revoked_at', 'expires_at'], 'idx_sessions_user_active');
            $table->foreign('user_id', 'fk_sessions_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141750_create_user_otps_table.php

SHA256: 2A6DA8C68DEC3EC9C069A80D2B28C21B068DE73B087B7BC122E061D91EA9B2E0

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_otps', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_unicode_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('purpose', 100);
            $table->string('code_hash', 255);
            $table->dateTime('expires_at');
            $table->dateTime('used_at')->nullable();
            $table->unsignedTinyInteger('attempts')->default(0);
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'purpose', 'expires_at', 'used_at'], 'idx_user_otps_lookup');
            $table->foreign('user_id', 'fk_user_otps_user')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('user_otps');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141751_create_payout_accounts_table.php

SHA256: 5057D4E6EA93822D22483FBD24CB0071BE983FC2F9AB72BCFC11C5343FAED441

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payout_accounts', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('provider', 100);
            $table->string('account_number', 100);
            $table->string('account_name', 255);
            $table->enum('status', ['pending_verification', 'verified', 'disabled'])->default('pending_verification');
            $table->boolean('is_default')->default(false);
            $table->dateTime('verified_at')->nullable();
            $table->dateTime('disabled_at')->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->unique(['user_id', 'provider', 'account_number'], 'uq_payout_accounts_user_provider_account');
            $table->index(['user_id', 'status'], 'idx_payout_accounts_user_status');
            $table->foreign('user_id', 'fk_payout_accounts_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_payout_accounts_default_bi`
BEFORE INSERT ON `payout_accounts`
FOR EACH ROW
BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
            SELECT 1
            FROM `payout_accounts`
            WHERE `user_id` = NEW.`user_id`
              AND `is_default` = 1
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END
SQL);

        DB::unprepared(<<<'SQL'
CREATE TRIGGER `trg_payout_accounts_default_bu`
BEFORE UPDATE ON `payout_accounts`
FOR EACH ROW
BEGIN
    IF NEW.is_default = 1 AND NEW.status <> 'verified' THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'Only a verified payout account can be default';
    END IF;

    IF NEW.is_default = 1
       AND EXISTS (
            SELECT 1
            FROM `payout_accounts`
            WHERE `user_id` = NEW.`user_id`
              AND `is_default` = 1
              AND `id` <> OLD.`id`
       )
    THEN
        SIGNAL SQLSTATE '45000'
        SET MESSAGE_TEXT = 'A user can have only one default payout account';
    END IF;
END
SQL);

    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_payout_accounts_default_bu`');
        DB::unprepared('DROP TRIGGER IF EXISTS `trg_payout_accounts_default_bi`');
        Schema::dropIfExists('payout_accounts');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141751_create_withdraw_requests_table.php

SHA256: B15A6381259CEA2A8292B668716D415D540004D131152F1DF8FC5EB053043174

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdraw_requests', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('payout_account_id');
            $table->decimal('amount', 15, 2);
            $table->enum('status', ['pending', 'approved', 'processing', 'manual_required', 'paid', 'rejected', 'cancelled', 'failed'])->default('pending')->comment('pending=chờ duyệt; approved=admin đã duyệt; processing=đang chi trả; manual_required=cần admin xử lý thủ công; paid=đã thanh toán; rejected=admin từ chối; cancelled=giảng viên tự hủy khi còn pending; failed=chi trả thất bại cuối cùng');
            $table->dateTime('requested_at')->useCurrent();
            $table->dateTime('approved_at')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->dateTime('processed_at')->nullable();
            $table->string('provider_payout_id', 191)->nullable()->unique('uq_withdraw_provider_payout_id');
            $table->string('failure_reason', 1000)->nullable();
            $table->string('rejected_reason', 1000)->nullable();
            $table->string('admin_note', 1000)->nullable();
            $table->string('account_number_snapshot', 100);
            $table->string('account_name_snapshot', 255);
            $table->decimal('available_balance_before', 15, 2);
            $table->decimal('available_balance_after', 15, 2);
            $table->string('bank_name_snapshot', 255);
            $table->string('payout_provider', 100)->nullable();
            $table->dateTime('created_at')->useCurrent();
            $table->dateTime('updated_at')->useCurrent()->useCurrentOnUpdate();
            
            $table->index(['user_id', 'status', 'requested_at'], 'idx_withdraw_user_status');
            $table->index('payout_account_id', 'idx_withdraw_payout_account');
            $table->foreign('payout_account_id', 'fk_withdraw_payout_account')->references('id')->on('payout_accounts')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('user_id', 'fk_withdraw_user')->references('id')->on('users')->restrictOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('withdraw_requests');
    }
};
```

---

## FILE: database\migrations\2026_08_25_141752_create_withdrawal_revenues_table.php

SHA256: A63041F6CE4DF72C968F94ACE7A8D9DA4195D2B4A58FE1BB7F28C190DED0F7E6

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('withdrawal_revenues', function (Blueprint $table) {
            $table->charset = 'utf8mb4';
            $table->collation = 'utf8mb4_vietnamese_ci';

            $table->unsignedBigInteger('withdrawal_id');
            $table->unsignedBigInteger('revenue_id');
            $table->decimal('allocated_amount', 15, 2);
            $table->dateTime('created_at')->useCurrent();
            $table->primary(['withdrawal_id', 'revenue_id']);
            $table->index('revenue_id', 'idx_withdrawal_revenues_revenue');
            $table->foreign('revenue_id', 'fk_withdrawal_revenues_revenue')->references('id')->on('revenues')->restrictOnDelete()->cascadeOnUpdate();
            $table->foreign('withdrawal_id', 'fk_withdrawal_revenues_withdrawal')->references('id')->on('withdraw_requests')->cascadeOnDelete()->cascadeOnUpdate();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('withdrawal_revenues');
    }
};
```


# MODELS

Total files: 25

---

## FILE: app\Models\Banner.php

SHA256: 156AD7BA990428E507FEE10C7A94295BEC772EC74FEE45666D1A89FC70C25141

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'title',
        'image_url',
        'image_public_id',
        'target_url',
        'position',
        'sort_order',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }
}
```

---

## FILE: app\Models\Category.php

SHA256: 692F21353D1BCC40ABA02DBF8493101257A8DCC339573DD99FF589846452F53F

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'parent_id',
        'name',
        'slug',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_categories', 'category_id', 'course_id');
    }
}
```

---

## FILE: app\Models\Comment.php

SHA256: A63C3B42E2CBBC678DE145344C07A02D2168E15A4684BCBA98CBC62D6A0C5222

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    public const STATUS_VISIBLE = 'visible';
    public const STATUS_HIDDEN = 'hidden';
    public const STATUS_DELETED = 'deleted';

    protected $fillable = [
        'parent_id',
        'enrollment_id',
        'user_id',
        'lesson_id',
        'content',
        'status',
        'is_official',
    ];

    protected function casts(): array
    {
        return [
            'parent_id' => 'integer',
            'enrollment_id' => 'integer',
            'user_id' => 'integer',
            'lesson_id' => 'integer',
            'is_official' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
```

---

## FILE: app\Models\CommissionRule.php

SHA256: AABC9DA8679F6B06CD6E5B4C5048C22260D7E4A4FD46E1200DD34BC34222EDAA

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CommissionRule extends Model
{
    protected $fillable = [
        'name',
        'description',
        'instructor_rate',
        'platform_rate',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'instructor_rate' => 'decimal:4',
            'platform_rate' => 'decimal:4',
            'is_active' => 'boolean',
        ];
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }
}
```

---

## FILE: app\Models\Coupon.php

SHA256: F38ED164C721B02FE6228F8CCA5992DBDC6E80AF9DC0D3B3795E68978A49D95D

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    public const TYPE_PERCENT = 'percent';
    public const TYPE_FIXED = 'fixed';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_EXPIRED = 'expired';
    public const STATUS_USED_UP = 'used_up';

    protected $fillable = [
        'code',
        'course_id',
        'discount_type',
        'discount_value',
        'usage_limit',
        'used_count',
        'start_at',
        'end_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'discount_value' => 'decimal:2',
            'usage_limit' => 'integer',
            'used_count' => 'integer',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
```

---

## FILE: app\Models\Course.php

SHA256: 7C845F66D846FAE47FC72310B064CE48D3589D18E873E321B6061CE4BECCFF4A

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Course extends Model
{
    use HasFactory;

    public const LEVEL_BEGINNER = 'beginner';
    public const LEVEL_INTERMEDIATE = 'intermediate';
    public const LEVEL_ADVANCED = 'advanced';
    public const LEVEL_ALL = 'all_levels';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_REVIEW = 'pending_review';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'instructor_id',
        'title',
        'slug',
        'short_description',
        'description',
        'thumbnail_url',
        'thumbnail_public_id',
        'intro_video_url',
        'intro_video_id',
        'price',
        'discount_percent',
        'course_level',
        'language',
        'requirements',
        'outcomes',
        'status',
        'is_featured',
        'published_at',
        'reviewed_by',
        'admin_reject_reason',
    ];

    protected function casts(): array
    {
        return [
            'instructor_id' => 'integer',
            'price' => 'decimal:2',
            'discount_percent' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'requirements' => 'array',
            'outcomes' => 'array',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'reviewed_by' => 'integer',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'course_categories', 'course_id', 'category_id');
    }

    public function coupon(): HasOne
    {
        return $this->hasOne(Coupon::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CourseSection::class)->orderBy('sort_order');
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class);
    }

    public function faqs(): BelongsToMany
    {
        return $this->belongsToMany(Faq::class, 'course_faqs', 'course_id', 'faq_id')
            ->withPivot('sort_order')
            ->orderBy('course_faqs.sort_order');
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistedBy(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'wishlist', 'course_id', 'user_id')
            ->withPivot('created_at');
    }

    public function reviews(): HasManyThrough
    {
        return $this->hasManyThrough(
            CourseReview::class,
            Order::class,
            'course_id',
            'order_id',
            'id',
            'id'
        );
    }
}
```

---

## FILE: app\Models\CourseReview.php

SHA256: 69E8DA7F6E982EB967A5132D524869AC97E5DBBD8F66E6627628558CD9DF5BD8

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CourseReview extends Model
{
    protected $fillable = [
        'order_id',
        'rating',
        'comment',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'order_id' => 'integer',
            'rating' => 'integer',
            'edited_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
```

---

## FILE: app\Models\CourseSection.php

SHA256: CC4810D3F6A59E7828459D8D04091AD2963072B978355FB3A25F253BF2D7D29A

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CourseSection extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'course_id' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_section_id')->orderBy('sort_order');
    }
}
```

---

## FILE: app\Models\Enrollment.php

SHA256: 1EEE0167F05D3A08F4409D96AD110D682501E113A3E16B4ECE68ED3F33F62B25

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Enrollment extends Model
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'user_id',
        'course_id',
        'order_id',
        'status',
        'progress_percent',
        'enrolled_at',
        'expires_at',
        'completed_at',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'order_id' => 'integer',
            'progress_percent' => 'decimal:2',
            'enrolled_at' => 'datetime',
            'expires_at' => 'datetime',
            'completed_at' => 'datetime',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function lessonProgress(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function videoProgress(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function lessonNotes(): HasMany
    {
        return $this->hasMany(LessonNote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

---

## FILE: app\Models\Faq.php

SHA256: 62A096DECB13252384D2582C82D7C18EBE530E6175D36A55606D48CEE5A4A3D5

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Faq extends Model
{
    public const SOURCE_SYSTEM = 'system';
    public const SOURCE_INSTRUCTOR = 'instructor';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    protected $fillable = [
        'question',
        'answer',
        'type',
        'source',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_faqs', 'faq_id', 'course_id')
            ->withPivot('sort_order')
            ->orderBy('course_faqs.sort_order');
    }
}
```

---

## FILE: app\Models\InstructorProfile.php

SHA256: 88126F91A4CE12FCE9CB48F15331DD0A581755CBEB13030F61BF223E02CD31E8

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InstructorProfile extends Model
{
    public const RANK_BRONZE = 'bronze';
    public const RANK_SILVER = 'silver';
    public const RANK_GOLD = 'gold';
    public const RANK_DIAMOND = 'diamond';

    protected $fillable = [
        'user_id',
        'bio',
        'expertise',
        'experience_years',
        'instructor_rank',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'experience_years' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## FILE: app\Models\Lesson.php

SHA256: 44791C88B66B9630B1C25658C9C52BAEEAA46E87AC040AE858895F0ABEC07D9C

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Lesson extends Model
{
    public const TYPE_VIDEO = 'video';
    public const TYPE_TEXT = 'text';
    public const TYPE_DOCUMENT = 'document';

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_HIDDEN = 'hidden';

    protected $fillable = [
        'course_section_id',
        'course_id',
        'title',
        'lesson_type',
        'content',
        'video_url',
        'video_id',
        'video_duration_seconds',
        'is_preview',
        'status',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'course_section_id' => 'integer',
            'course_id' => 'integer',
            'video_duration_seconds' => 'integer',
            'is_preview' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(CourseSection::class, 'course_section_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(LessonAsset::class);
    }

    public function progressRecords(): HasMany
    {
        return $this->hasMany(LessonProgress::class);
    }

    public function videoProgressRecords(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
    }

    public function notes(): HasMany
    {
        return $this->hasMany(LessonNote::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }
}
```

---

## FILE: app\Models\LessonAsset.php

SHA256: DB048092D7BDA8688A963DFC12C90F336D3E816BA79A73C50484036E719BA250

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonAsset extends Model
{
    protected $fillable = [
        'lesson_id',
        'title',
        'file_url',
        'file_id',
        'file_name',
        'file_type',
        'file_size',
        'note',
    ];

    protected function casts(): array
    {
        return [
            'lesson_id' => 'integer',
            'file_size' => 'integer',
        ];
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
```

---

## FILE: app\Models\LessonNote.php

SHA256: 6F2EBF040C693A319957FBDDAFDF9F1C71D5ADDE7BE33F3B7AE4493CB72B4769

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonNote extends Model
{
    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'content',
        'note_time_second',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'lesson_id' => 'integer',
            'note_time_second' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
```

---

## FILE: app\Models\LessonProgress.php

SHA256: A8DAFE42B9592BFC2A95A4B04A719398CA5D78A0DB09B838F99362FD0C858AC8

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LessonProgress extends Model
{
    public const STATUS_NOT_STARTED = 'not_started';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    protected $table = 'lesson_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'status',
        'started_at',
        'completed_at',
        'learning_duration_seconds',
        'last_accessed_at',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'lesson_id' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'learning_duration_seconds' => 'integer',
            'last_accessed_at' => 'datetime',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
```

---

## FILE: app\Models\Notification.php

SHA256: 6425215B037C781A3C8BB16155C4E73DEDEFA3514E14DD87E9FE2D872E4BA71F

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    public const CHANNEL_WEB = 'web';
    public const CHANNEL_EMAIL = 'email';
    public const CHANNEL_BOTH = 'both';

    public const EMAIL_PENDING = 'pending';
    public const EMAIL_SENT = 'sent';
    public const EMAIL_FAILED = 'failed';
    public const EMAIL_SKIPPED = 'skipped';

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'data',
        'action_url',
        'channel',
        'read_at',
        'email_status',
        'email_sent_at',
        'email_error',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'data' => 'array',
            'read_at' => 'datetime',
            'email_sent_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## FILE: app\Models\Order.php

SHA256: 2AECB6E38AD4DCBF318B1DB1B0544CFE8C469103CC8C215BA76EC00975A922E0

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    public const STATUS_PENDING_PAYMENT = 'pending_payment';
    public const STATUS_PAID = 'paid';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';
    public const STATUS_EXPIRED = 'expired';

    public const PAYMENT_PENDING = 'pending';
    public const PAYMENT_PAID = 'paid';
    public const PAYMENT_FAILED = 'failed';
    public const PAYMENT_EXPIRED = 'expired';

    protected $fillable = [
        'order_code',
        'user_id',
        'course_id',
        'coupon_id',
        'commission_rule_id',
        'status',
        'payment_status',
        'price_snapshot',
        'discount_amount',
        'amount',
        'payment_method',
        'provider_transaction_id',
        'paid_at',
        'expires_at',
        'cancelled_reason',
        'failed_reason',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'coupon_id' => 'integer',
            'commission_rule_id' => 'integer',
            'price_snapshot' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function enrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class);
    }

    public function revenue(): HasOne
    {
        return $this->hasOne(Revenue::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(CourseReview::class);
    }
}
```

---

## FILE: app\Models\PayoutAccount.php

SHA256: F9884F65BD5F04497320B107117B2D0C787726452956B633EDE7730028C9EC66

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PayoutAccount extends Model
{
    public const STATUS_PENDING_VERIFICATION = 'pending_verification';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_DISABLED = 'disabled';

    protected $fillable = [
        'user_id',
        'provider',
        'account_number',
        'account_name',
        'status',
        'is_default',
        'verified_at',
        'disabled_at',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'is_default' => 'boolean',
            'verified_at' => 'datetime',
            'disabled_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class);
    }
}
```

---

## FILE: app\Models\Revenue.php

SHA256: 61EFEA961E117F9D829AC30DBE2E03DA0B9EC185CC57804CB4C18406BFD75205

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Revenue extends Model
{
    protected $fillable = [
        'instructor_id',
        'course_id',
        'order_id',
        'gross_amount',
        'instructor_amount',
        'platform_fee_amount',
        'commission_rule_id',
        'earned_at',
    ];

    protected function casts(): array
    {
        return [
            'instructor_id' => 'integer',
            'course_id' => 'integer',
            'order_id' => 'integer',
            'commission_rule_id' => 'integer',
            'gross_amount' => 'decimal:2',
            'instructor_amount' => 'decimal:2',
            'platform_fee_amount' => 'decimal:2',
            'earned_at' => 'datetime',
        ];
    }

    public function instructor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'instructor_id');
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function commissionRule(): BelongsTo
    {
        return $this->belongsTo(CommissionRule::class);
    }

    public function withdrawalRequests(): BelongsToMany
    {
        return $this->belongsToMany(
            WithdrawRequest::class,
            'withdrawal_revenues',
            'revenue_id',
            'withdrawal_id'
        )->withPivot(['allocated_amount', 'created_at']);
    }
}
```

---

## FILE: app\Models\Session.php

SHA256: F72F4BDC881E9887B27A29D30435E170438D838F8EDB10BDD79F25A38BD4BFF9

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Session extends Model
{
    protected $table = 'sessions';

    protected $fillable = [
        'user_id',
        'refresh_token_hash',
        'device_name',
        'ip_address',
        'user_agent',
        'expires_at',
        'revoked_at',
    ];

    protected $hidden = [
        'refresh_token_hash',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'expires_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## FILE: app\Models\User.php

SHA256: 0C4F37DD3ABBEB3F27281F9C0819FB755B2C93F99F20050EE44ABFDEFF8746BB

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_LEARNER = 'learner';
    public const ROLE_INSTRUCTOR = 'instructor';
    public const ROLE_ADMIN = 'admin';

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_SUSPENDED = 'suspended';

    protected $table = 'users';

    protected $fillable = [
        'full_name',
        'email',
        'phone',
        'password_hash',
        'avatar_url',
        'avatar_public_id',
        'role',
        'status',
        'locked',
        'locked_reason',
        'email_verified_at',
        'last_login_at',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected function casts(): array
    {
        return [
            'locked' => 'boolean',
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    public function getAuthPassword(): string
    {
        return (string) $this->password_hash;
    }

    public function getRememberTokenName(): ?string
    {
        return null;
    }

    public function instructorProfile(): HasOne
    {
        return $this->hasOne(InstructorProfile::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'instructor_id');
    }

    public function reviewedCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'reviewed_by');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function payoutAccounts(): HasMany
    {
        return $this->hasMany(PayoutAccount::class);
    }

    public function withdrawRequests(): HasMany
    {
        return $this->hasMany(WithdrawRequest::class);
    }

    public function revenues(): HasMany
    {
        return $this->hasMany(Revenue::class, 'instructor_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class);
    }

    public function otps(): HasMany
    {
        return $this->hasMany(UserOtp::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function wishlistCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'wishlist', 'user_id', 'course_id')
            ->withPivot('created_at');
    }
}
```

---

## FILE: app\Models\UserOtp.php

SHA256: AE79FCCB9730CA1A18FD1C42002339482B76D97FFCD1A6DB0F21370EB81D6A70

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserOtp extends Model
{
    protected $table = 'user_otps';

    protected $fillable = [
        'user_id',
        'purpose',
        'code_hash',
        'expires_at',
        'used_at',
        'attempts',
    ];

    protected $hidden = [
        'code_hash',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'expires_at' => 'datetime',
            'used_at' => 'datetime',
            'attempts' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

## FILE: app\Models\VideoProgress.php

SHA256: 153EFB8CE8D83EBA1304B3DAC293B8C559407B52DC4E1AEC1B276ADD60EC2707

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = [
        'enrollment_id',
        'lesson_id',
        'current_second',
    ];

    protected function casts(): array
    {
        return [
            'enrollment_id' => 'integer',
            'lesson_id' => 'integer',
            'current_second' => 'integer',
        ];
    }

    public function enrollment(): BelongsTo
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class);
    }
}
```

---

## FILE: app\Models\Wishlist.php

SHA256: 66AB7E2D43C1F81E5EB64B33C7F62F56D7082046EAC6BC40350703AB0EB96D69

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Wishlist extends Model
{
    protected $table = 'wishlist';

    public $incrementing = false;

    public const UPDATED_AT = null;

    protected $fillable = [
        'user_id',
        'course_id',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'course_id' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
```

---

## FILE: app\Models\WithdrawRequest.php

SHA256: 971A04C03968759BD1EA5642DD5852F52F9B2B2AA13CA4D88CC3D6ABCF1C83E0

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class WithdrawRequest extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_MANUAL_REQUIRED = 'manual_required';
    public const STATUS_PAID = 'paid';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_FAILED = 'failed';

    protected $table = 'withdraw_requests';

    protected $fillable = [
        'user_id',
        'payout_account_id',
        'amount',
        'status',
        'requested_at',
        'approved_at',
        'paid_at',
        'processed_at',
        'provider_payout_id',
        'failure_reason',
        'rejected_reason',
        'admin_note',
        'account_number_snapshot',
        'account_name_snapshot',
        'available_balance_before',
        'available_balance_after',
        'bank_name_snapshot',
        'payout_provider',
    ];

    protected function casts(): array
    {
        return [
            'user_id' => 'integer',
            'payout_account_id' => 'integer',
            'amount' => 'decimal:2',
            'requested_at' => 'datetime',
            'approved_at' => 'datetime',
            'paid_at' => 'datetime',
            'processed_at' => 'datetime',
            'available_balance_before' => 'decimal:2',
            'available_balance_after' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function payoutAccount(): BelongsTo
    {
        return $this->belongsTo(PayoutAccount::class);
    }

    public function revenues(): BelongsToMany
    {
        return $this->belongsToMany(
            Revenue::class,
            'withdrawal_revenues',
            'withdrawal_id',
            'revenue_id'
        )->withPivot(['allocated_amount', 'created_at']);
    }
}
```


# QUICK FILE LIST

- database\migrations\2026_08_25_141737_create_users_table.php
- database\migrations\2026_08_25_141738_create_banners_table.php
- database\migrations\2026_08_25_141738_create_categories_table.php
- database\migrations\2026_08_25_141739_create_commission_rules_table.php
- database\migrations\2026_08_25_141739_create_courses_table.php
- database\migrations\2026_08_25_141740_create_instructor_profiles_table.php
- database\migrations\2026_08_25_141741_create_coupons_table.php
- database\migrations\2026_08_25_141741_create_course_sections_table.php
- database\migrations\2026_08_25_141742_create_course_categories_table.php
- database\migrations\2026_08_25_141742_create_lessons_table.php
- database\migrations\2026_08_25_141743_create_faqs_table.php
- database\migrations\2026_08_25_141744_create_course_faqs_table.php
- database\migrations\2026_08_25_141744_create_orders_table.php
- database\migrations\2026_08_25_141745_create_course_reviews_table.php
- database\migrations\2026_08_25_141745_create_enrollments_table.php
- database\migrations\2026_08_25_141745_create_revenues_table.php
- database\migrations\2026_08_25_141746_create_lesson_assets_table.php
- database\migrations\2026_08_25_141747_create_lesson_progress_table.php
- database\migrations\2026_08_25_141747_create_video_progress_table.php
- database\migrations\2026_08_25_141748_create_comments_table.php
- database\migrations\2026_08_25_141748_create_lesson_notes_table.php
- database\migrations\2026_08_25_141749_create_notifications_table.php
- database\migrations\2026_08_25_141749_create_wishlist_table.php
- database\migrations\2026_08_25_141750_create_sessions_table.php
- database\migrations\2026_08_25_141750_create_user_otps_table.php
- database\migrations\2026_08_25_141751_create_payout_accounts_table.php
- database\migrations\2026_08_25_141751_create_withdraw_requests_table.php
- database\migrations\2026_08_25_141752_create_withdrawal_revenues_table.php
- app\Models\Banner.php
- app\Models\Category.php
- app\Models\Comment.php
- app\Models\CommissionRule.php
- app\Models\Coupon.php
- app\Models\Course.php
- app\Models\CourseReview.php
- app\Models\CourseSection.php
- app\Models\Enrollment.php
- app\Models\Faq.php
- app\Models\InstructorProfile.php
- app\Models\Lesson.php
- app\Models\LessonAsset.php
- app\Models\LessonNote.php
- app\Models\LessonProgress.php
- app\Models\Notification.php
- app\Models\Order.php
- app\Models\PayoutAccount.php
- app\Models\Revenue.php
- app\Models\Session.php
- app\Models\User.php
- app\Models\UserOtp.php
- app\Models\VideoProgress.php
- app\Models\Wishlist.php
- app\Models\WithdrawRequest.php
