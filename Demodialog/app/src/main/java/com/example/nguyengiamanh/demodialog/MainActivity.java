package com.example.nguyengiamanh.demodialog;

import android.app.AlarmManager;
import android.app.AlertDialog;
import android.content.DialogInterface;
import android.os.Bundle;
import android.support.design.widget.FloatingActionButton;
import android.support.design.widget.Snackbar;
import android.support.v7.app.AppCompatActivity;
import android.support.v7.widget.Toolbar;
import android.view.View;
import android.view.Menu;
import android.view.MenuItem;
import android.widget.Button;
import android.widget.EditText;

public class MainActivity extends AppCompatActivity {

   EditText edtuser,edtpassword;
    Button btndangki,btndangnhap,btnthoat;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_main);
        anhxa();
        controButton();
        }

    private void controButton() {
        btnthoat.setOnClickListener(new View.OnClickListener() {
            @Override
            public void onClick(View v) {
                AlertDialog.Builder builder = new AlertDialog.Builder(MainActivity.this,android.R.style.Theme_DeviceDefault_Light_Dialog);
                builder.setTitle("ban co muon that su thoay khoi app");
                builder.setMessage("ban hay lam theo huon dan ben duoi");
                builder.setIcon(android.R.drawable.ic_dialog_alert);
                builder.setPositiveButton("co", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {
                        onBackPressed();
                    }
                });
                builder.setNegativeButton("khong", new DialogInterface.OnClickListener() {
                    @Override
                    public void onClick(DialogInterface dialog, int which) {

                    }
                });
                builder.show();
            }

        });
    }


    private void anhxa() {
                    edtuser = (EditText)findViewById(R.id.edittextuser);
                    edtpassword = (EditText)findViewById(R.id.edittextPassword);
                    btndangki = (Button)findViewById(R.id.buttondangki);
                    btndangnhap = (Button)findViewById(R.id.buttondangnhap);
                    btnthoat = (Button)findViewById(R.id.buttonThoat);
                }

    }

