#!/bin/bash

function Chuli_wenjian(){
# 删除/**/中的注释（在同一行）
    sed -i '' 's/\/\*.*\*\///' $1 2>> log_warning
# 删除/**/中的注释（不在同一行）
    sed -i '' '/\/\*/,/\*\//d' $1 2>> log_warning
# 删除//后面的注释
    sed -i '' 's/\/\/.*//' $1 2>> log_warning
# 删除行末尾的空格
    sed -i '' 's/ *$//' $1 2>> log_warning
# 删除空行
    sed -i '' '/^ *$/d' $1 2>> log_warning
# 处理字符'\'、'''、'<'
    sed -i '' 's/\\/\\\\/'g $1 2>> log_warning
    sed -i '' "s/\'/\\\'/"g $1 2>> log_warning
    sed -i '' 's/</\&lt/'g $1 2>> log_warning
# 将所有的函数导出到同一个文件
    sed -n '/^[a-z].*[a-z|*]$/,/^}/p' $1 > $2  2>> log_warning
# 将所有的数据结构导出到同一个文件
    sed -n '/^struct.*{$/,/^}/p' $1  2>> log_warning | sed 's/}.*;/};/' > $3  2>> log_warning
}

# 过滤掉不需要遍历的目录
function Guolv_mulu(){
    wenjian=$(echo '/'$1'/')
    mulu=$(echo '/'$2'/')
    if [[ $mulu =~ $wenjian ]]; then
        return 0
    fi
    return 1
}

function Bianli_mulu(){
    for wenjian in `ls $1`
    do
        lujing=$1"/"$wenjian
        if [ -d $lujing ]; then
            Guolv_mulu $wenjian $3
            declare -i ret=$?
            if [ $ret == 1 ]; then
                Bianli_mulu $lujing $2 $3
            fi
        else
            if [[ $lujing =~ \.c ]] || [[ $lujing =~ \.h ]]; then
                echo '    '$lujing
# 将所有的.c和.h文件的内容导出到同一个文件
                cat $lujing >> $2
            fi
        fi
    done
}

declare -i num_hanshu=1

function Create_SQL(){
    declare -i num_line=0
    hanshu_pattern="^[A-Za-z_].*) *{$"
    shuju_pattern="^[A-Za-z_].*[A-Za-z_] *{$"
    end="^}"

    ifs=$IFS
    IFS=
    while read -r line
    do
# 为函数的代码和数据结构的字段创建SQL语句
        echo "INSERT INTO dai_ma SET han_shu = $num_hanshu, xu_hao = $num_line, zhu_shi = 0, wen_ti = 0, nei_rong = '$line', shi_jian = now(), gl_1 = 0, shj_ch = now();" >> $3
        if [[ $line =~ $hanshu_pattern ]]; then
            name=$(echo $line | cut -d '(' -f 1)
            echo $name >> $4
            echo '    function: '$name
        elif [[ $line =~ $shuju_pattern ]]; then
            echo $name >> $4
            name=$(echo $line | cut -d ' ' -f 2)
            echo '    datestruct: '$name
        fi

        if [[ $line =~ $end ]]
        then
            num_line=$num_line+1
# 为函数和数据结构创建SQL语句
            echo "INSERT INTO han_shu SET id=$num_hanshu, ming_zi='$name', dai_ma=$num_line, mo_kuai='';" >> $2
            num_hanshu=$num_hanshu+1
            num_line=-1
        fi
        num_line=$num_line+1
    done < $1
    IFS=$ifs
}

function Chachong(){
# 打印重复的函数和数据结构及重复次数
    sort $1  2>> log_warning | uniq -c | awk '{if($1>1) print}'
}

rm -f hanshu.sql daima.sql

echo "traversing all files..."
Bianli_mulu $1 tmp $2
echo "ok..."
Chuli_wenjian tmp hanshu shuju
echo "creating SQL of function..."
Create_SQL hanshu hanshu.sql daima.sql list_hanshu
echo "ok..."
echo "creating SQL of datestruct..."
Create_SQL shuju hanshu.sql daima.sql list_shuju
echo "ok..."

echo "Check results of function:"
Chachong list_hanshu

echo "Check results of datastructure:"
Chachong list_shuju

echo '----end-----'$(date '+%Y-%m-%d %H:%M:%S')'---------' >> log_warning

rm -f tmp hanshu shuju list_hanshu list_shuju
