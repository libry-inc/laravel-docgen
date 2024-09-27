<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private array $tables;

    private array $foreignKeys = [
        'members' => [
            ['group_id', 'groups', 'id', 'cascadeOnDelete', 'cascadeOnUpdate'],
            ['user_id', 'users', 'id', 'cascadeOnDelete', 'cascadeOnUpdate'],
        ],
    ];

    public function __construct()
    {
        $this->tables = [
            'users' => static function (Blueprint $table): void {
                $table->comment("User table\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nulla vel neque luctus ullamcorper. Ut et mi vel lectus gravida finibus eu a nisl. Aenean maximus felis sed augue tempor ornare. Aenean elementum nibh vel diam rhoncus, a sodales nulla bibendum. In hac habitasse platea dictumst. Proin lacinia tellus ut sem sodales pellentesque. Etiam nisl leo, porttitor nec viverra nec, finibus eu nibh. Donec pharetra lorem felis, eu fringilla purus elementum vel. Integer consequat a risus non ultricies. Sed erat nibh, feugiat id accumsan eu, dictum eu diam. Quisque bibendum pretium mauris sed ullamcorper. Morbi a tincidunt diam. Nullam.");
                $table->increments('id')->comment('User ID');
            },
            'groups' => static function (Blueprint $table): void {
                $table->comment("Group table\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nulla vel neque luctus ullamcorper. Ut et mi vel lectus gravida finibus eu a nisl. Aenean maximus felis sed augue tempor ornare. Aenean elementum nibh vel diam rhoncus, a sodales nulla bibendum. In hac habitasse platea dictumst. Proin lacinia tellus ut sem sodales pellentesque. Etiam nisl leo, porttitor nec viverra nec, finibus eu nibh. Donec pharetra lorem felis, eu fringilla purus elementum vel. Integer consequat a risus non ultricies. Sed erat nibh, feugiat id accumsan eu, dictum eu diam. Quisque bibendum pretium mauris sed ullamcorper. Morbi a tincidunt diam. Nullam.");
                $table->increments('id')->comment('Group ID');
            },
            'members' => static function (Blueprint $table): void {
                $table->comment("Member table\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nulla vel neque luctus ullamcorper. Ut et mi vel lectus gravida finibus eu a nisl. Aenean maximus felis sed augue tempor ornare. Aenean elementum nibh vel diam rhoncus, a sodales nulla bibendum. In hac habitasse platea dictumst. Proin lacinia tellus ut sem sodales pellentesque. Etiam nisl leo, porttitor nec viverra nec, finibus eu nibh. Donec pharetra lorem felis, eu fringilla purus elementum vel. Integer consequat a risus non ultricies. Sed erat nibh, feugiat id accumsan eu, dictum eu diam. Quisque bibendum pretium mauris sed ullamcorper. Morbi a tincidunt diam. Nullam.");
                $table->increments('id')->comment('Member ID');
                $table->unsignedInteger('group_id');
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('role_id');
            },
            'roles' => static function (Blueprint $table): void {
                $table->comment("Role table\nLorem ipsum dolor sit amet, consectetur adipiscing elit. Nullam nec nulla vel neque luctus ullamcorper. Ut et mi vel lectus gravida finibus eu a nisl. Aenean maximus felis sed augue tempor ornare. Aenean elementum nibh vel diam rhoncus, a sodales nulla bibendum. In hac habitasse platea dictumst. Proin lacinia tellus ut sem sodales pellentesque. Etiam nisl leo, porttitor nec viverra nec, finibus eu nibh. Donec pharetra lorem felis, eu fringilla purus elementum vel. Integer consequat a risus non ultricies. Sed erat nibh, feugiat id accumsan eu, dictum eu diam. Quisque bibendum pretium mauris sed ullamcorper. Morbi a tincidunt diam. Nullam.");
                $table->increments('id')->comment('Role ID');
                $table->string('value');
            },
            'tests' => static function (Blueprint $table): void {
                $table->comment("Tests table\nFor checking available types conversion.");
                $table->bigInteger('int64')->autoIncrement();
                $table->integer('int32')->default(123);
                $table->smallInteger('int16');
                $table->tinyInteger('int8');
                $table->unsignedBigInteger('uint64');
                $table->unsignedInteger('uint32');
                $table->unsignedSmallInteger('uint16');
                $table->unsignedTinyInteger('uint8');
                $table->decimal('dec');
                $table->decimal('dec_with', 6, 3);
                $table->float('float')->comment("\nalias double");
                $table->double('double');
                $table->boolean('bool');
                $table->binary('bin')->nullable();
                $table->binary('limited_bin', 128);
                $table->binary('fixed_bin', 32, true);
                $table->date('date');
                $table->dateTime('datetime')->useCurrent();
                $table->dateTime('datetime_ms', 3)->useCurrentOnUpdate();
                $table->time('time')->default('12:00:00');
                $table->time('time_ms', 3);
                $table->string('str')->default("abc\ndef");
                $table->string('limited_str', 128);
                $table->char('default_char');
                $table->char('char16', 16);
                $table->longText('text64');
                $table->mediumText('text32');
                $table->text('text16');
                $table->tinyText('text8');
                $table->enum('enum', ['A', 'B', 'C']);
                $table->index(['int16', 'int8']);
                $table->index('int8');
                $table->unique(['uint16', 'uint8']);
                $table->unique('uint8');
            },
        ];
    }

    public function up(): void
    {
        $this->down();

        foreach ($this->tables as $table => $callback) {
            Schema::create($table, $callback);
        }

        foreach ($this->foreignKeys as $table => $foreignKeys) {
            Schema::table($table, static function (Blueprint $table) use ($foreignKeys): void {
                foreach ($foreignKeys as $name => $foreignKey) {
                    $foreignKeyDefinition = is_numeric($name)
                        ? $table->foreign(array_shift($foreignKey))
                        : $table->foreign(array_shift($foreignKey), $name);
                    $foreignKeyDefinition->on(array_shift($foreignKey))->references(array_shift($foreignKey));

                    foreach ($foreignKey as $method) {
                        $foreignKeyDefinition->{$method}();
                    }
                }
            });
        }

        DB::table('roles')->insert([
            ['id' => 1, 'value' => 'Administrator'],
            ['id' => 2, 'value' => 'Developer'],
            ['id' => 3, 'value' => 'Reader'],
        ]);
    }

    public function down(): void
    {
        foreach ($this->foreignKeys as $table => $foreignKeys) {
            if (Schema::hasTable($table)) {
                try {
                    Schema::table($table, static function (Blueprint $table) use ($foreignKeys): void {
                        foreach ($foreignKeys as $name => $foreignKey) {
                            $table->dropForeign(is_numeric($name) ? (array) $foreignKey[0] : $name);
                        }
                    });
                } catch (Throwable $e) {
                    report($e);
                }
            }
        }

        foreach (array_keys($this->tables) as $table) {
            Schema::dropIfExists($table);
        }
    }
};
