<?php 
include __DIR__."/../vendor/autoload.php"; 

//////////////////// v5 /////////////////////////

#Conn->execute(STRING[,...args]);                //PDOStatement
#Conn->lastInsertId();                           //int
#Conn->debug;                                    //bool 
#Conn->scope();                                  //Transaction
#Conn->sql(TABLE,[PK])                           //Model{sql,pk}    

#Model->where(STRING)                            //self{sql,where STRING...}
#Model->whereAnd(STRING)                         //self{sql,and STRING...}
#Model->whereOr(STRING)                          //self{sql,or STRING...}
#Model->order(STRING)                            //self{sql,order by STRING...}
#Model->field(FIELD)                             //self{sql,select FIELD...}
#Model->find([PK])                               //self{sql,where PK=...} 
#Model->limit(OFFSET)                            //self{sql,limit OFFSET... } 

#Model->getIterator()  iterator_to_array(#Model) //self{sql,row} 
#Model->all()                                    //[...Model{table,row} ]          or []
#Model->all(FILED)                               //[...VALUE]                      or []
#Model->all(FN)                                  //[...FN() ]                      or []
#Model->keypair(KEY)                             //[...KEY=>Model{table,row}  ]    or []
#Model->keypair(KEY,FIELD)                       //[...KEY=>VALUE ]                or []
#Model->keypair(KEY,FN)                          //[...KEY=>FN() ]                 or []
#Model->count()                                  //int   parent.where支持关联查询??
#Model->avg()                                    //int   parent.where支持关联查询??
#Model->sum()                                    //int   parent.where支持关联查询??
#Model->max()                                    //int   parent.where支持关联查询??
#Model->mix()                                    //int   parent.where支持关联查询??


#Model->sql(TABLE,[PK])          #Model[TABLE]   //Model{sql,pk}             查询新集合
#Model->ref(TABLE,[PK],[PK=>FK]) #Model[REF]     //Model{sql,ref,pk}         查询新集合
#Model->ref(TABLE,[PK],[FK=>PK]) #Model[REF]     //Model{sql,ref,pk}         查询新集合
#Model->get(OFFSET)              #Model[OFFSET]  //Model{sql,ref,pk} or NULL 查询新集合(第一列) 
 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model self:          插入到集合
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasmany:       插入到集合?parent.pk 
#Model->insert(ARRAY,...)        #Model[]=...    //self Model hasone:        插入到集合?并执行设置parent.fk  
#Model->save(ARRAY)                              //self Model self:first     插入到集合 失败就 修改
#Model->save(ARRAY)                              //self Model hasmany:first  插入到集合 失败就 修改parent.pk 
#Model->save(ARRAY)                              //self Model hasone:first   插入到集合 失败就 修改并执行设置parent.fk
#Model->update(ARRAY)                            //self RowCount             根据当前model条件修改集合(可能空)
#Model->delete(TRUE)                             //self RowCount             根据当前model条件删除集合(可能空)
 
#Model->val(KEY)                 #Model[KEY]     //VALUE or PK or NULL       读取首行单列(第一列)
#Model->val(KEY,VALUE)           #Model[KEY]=... //VOID                      修改首行单列
#Model->val(REF,Model)           #Model[KEY]=... //VOID                      修改首行单列
#Model->toArray()                (array)#Model   //row                       读取首行
#Model->replace(ARRAY)           #Model[REF]=... //self Model self:first     覆盖首行(删除首行并插入) 
#Model->replace(ARRAY)           #Model[REF]=... //self Model hasmany:first  覆盖首行(删除首行并插入)parent.pk 
#Model->replace(ARRAY)           #Model[REF]=... //self Model hasone:first   覆盖首行(删除首行并插入)并执行设置parent.fk


//////////////////// v4 /////////////////////////

#Conn->execute(STRING[,...args]);     //PDOStatement
#Conn->lastInsertId();                //int
#Conn->debug;                         //bool 
#Conn->scope();                       //Transaction
#Conn->sql(TABLE,[...PK])             //Model{...}
   
#Model->get(INDEX)                    //Model{count=1}
#Model[INDEX]                         //Model{count=1}
#Model->load(...PK)                   //Model(count=1}
#Model(...Pk)                         //Model(count=1)
   
#Model->find(...PK)                   //self(where Pk and Pk ...)
#Model->limit(LIMIT,OFFSET)           //self{limit LIMIT offset OFFSET...}
#Model->where(STRING)                 //self{where STRING...}
#Model->whereAnd(STRING)              //self{and STRING...}
#Model->whereOr(STRING)               //self{or STRING...}
#Model->field(FIELD)                  //self{select FIELD...}
#Model->ref(TABLE,[...PK],[...REF])   //self{where REF in (TABLE.PK)...}
#Model[TABLE]                         //self{where REF in (TABLE.PK)...}
   
#Model->all()                         //[...Model{count=1}]
#Model->keypair()                     //[...PK=>Model{count=1}]
#Model->keypair(KEY)                  //[...KET=>Model{count=1}]
#Model->all(FIELD)                    //[...VALUE]
#Model->keypair(null,FIELD)           //[...PK=>VALUE]
#Model->keypair(KEY,FIELD)            //[...KET=>VALUE]

#Model->val()                         //PKV
#Model[NULL]                          //PKV
#Model->val(FIELD)                    //VALUE
#Model[FIELD]                         //VALUE
#Model->val(FIELD,VALUE)              //VALUE  changed
#Model[FIELD]=VALUE                   //VALUE  changed
   
#Model->update(ARRAY)      //RowCount
#Model->delete(BOOL)       //RowCount
#Model->replace(ARRAY)     //RowCount    
#Model->insert(ARRAY)      //Model


#Model->set(ARRAT)         //Model                非原子级操作(进程不安全(删除预定
#Model->save()             //self{unset(changed)} 非原子级操作(进程不安全(删除预定



///////////////////// v3 ////////////////////////

#Conn->execute(STRING[,...args]); #PDOStatement
#Conn->lastInsertId();            #int
#Conn->debug;                     #bool
#Conn->sql(TABLE,...PKS)          #SQL 
#Conn->scope();                   #Transaction

#SQL[INDEX]                 #Row/NULL
#SQL[FIELD]                 #mixed/NULL
#SQL[MODEL]                 #SQL/NULL
#SQL->get()                 #Row
#SQL->get(INDEX)            #Row 
#SQL->val()                 #array
#SQL->val(FIELD)            #mixed 
#SQL->ref(TABLE,PKS,REF)    #SQL 

#SQL(...PKV)                #ROW/NULL
#SQL->load(...PKV)          #ROW/Throw

#SQL->__call(AggregateFunction) #mixed
#SQL->count(FIELD)              #mixed
#SQL->sum(FIELD)                #mixed
#SQL->.....

#SQL->find(...PKV)          	  #SQL
#SQL->where(STRING[, ..._args]);  #SQL
#SQL->where(ARRAY);               #SQL
#SQL->and(STRING[, ..._args]);    #SQL
#SQL->and(ARRAY);                 #SQL
#SQL->or(STRING[, ..._args]);     #SQL
#SQL->or(ARRAY);                  #SQL
#SQL->order(STRING[,..._args]);   #SQL
#SQL->field(STRING);              #SQL
#SQL->limit(INT[,INT])            #SQL

#SQL->each(Model=>closer)   #void
#SQL->map(Model=>closer)    #array
#SQL->getIterator()         #iterator()=>Row
#SQL->getAllIterator()      #iterator()=>array
#SQL->all()                 #[array,array...]/[]  
#SQL->all(KEY)              #[VALUE,VALUE...]/[]  
#SQL->keypair(KEY)          #[KEY=>array,KEY=>array...]/[]
#SQL->keypair(KEY,VAL)      #[KEY=>VALUE,KEY=>VALUE...]/[] 

#SQL->insert(ARRAY);             #MODLE  
#SQL->insertMulit(ARRAY_LIST);   #int
#SQL->update(ARRAY);             #int
#SQL->delete([BOOL]);            #int 
#SQL->set(ARRAY);				 #SQL

#ROW[FIELD]                  #MIXED/NULL 
#ROW[MODEL]                  #Sql/NULL 
#ROW->val(FIELD)             #MIXED
#ROW->ref(TABLE,PKS,REF)     #SQL
#ROW->create()               #bool
#ROW->save([PKS])            #bool
#ROW->destroy([PKS])         #bool


# PHP <= 5.6 
# ERROR function and(){}
# ERROR function or(){} 


